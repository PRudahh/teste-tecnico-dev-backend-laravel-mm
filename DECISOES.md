# DECISOES.md — Decisões Técnicas

Decisões tomadas durante o desenvolvimento, incluindo ambiguidades identificadas no enunciado e as justificativas para cada escolha
que precisei implementar a medida que fui criando os endpoints requisitos do teste e das dificuldades que fui enfrentando na 
implementação.

---

## 1. Autenticação e controle de acesso

**Decisão:** Utilizei o Laravel Sanctum para autenticação e campo `role` (enum: `admin`, `financeiro`, `operacional`) na tabela `users` para controle de permissões.

**Justificativa:** O Sanctum é nativo do Laravel, dispensa dependências externas e oferece revogação granular de tokens — adequado para uma API interna com usuários fixos. Para as permissões, pacotes como Spatie/laravel-permission seriam excessivos para três perfis fixos; um enum simples com middleware `CheckRole` é suficiente, direto e fácil de manter.

---

## 2. Modelagem financeira e Regras de Negócio

**Decisão:** O valor total do contrato nunca é salvo no banco — sempre calculado via `SUM(quantidade * valor_unitario)` a partir dos itens. A data de vencimento das cobranças é definida como o **dia 10 do mês de referência**, armazenada explicitamente na cobrança. Cobranças com mesmo vencimento são priorizadas por `id ASC` na aplicação de crédito.

**Justificativa:** Entendi que o enunciado proibia explicitamente redundância no valor do contrato. O dia 10 é convenção comum no mercado brasileiro de serviços recorrentes e torna o vencimento previsível sem input manual — decisão necessária pois o enunciado não especificou esse campo. O desempate por `id ASC` garante comportamento determinístico na fila de cobranças.

---

## 3. Ciclo de vida das cobranças e proteção de domínio

**Decisão:** As transições de status foram encapsuladas no Value Object `StatusCobranca`, com um mapa explícito de transições válidas. Transições inválidas lançam `DomainException`, que é convertida em resposta HTTP 422 na camada de infraestrutura.

**Fluxo:**
```
pendente → aguardando_pagamento → pago
                              → pago_parcial → pago
                              → inadimplente → pago
                              → cancelado
```

**Justificativa:** Adotei a estratégia de separar a regra de negócio do framework, garantindo que ela seja testável de forma isolada e nunca seja violada silenciosamente, independente de por onde a transição seja chamada — controller, job ou comando Artisan.

---

## 4. Job assíncrono: idempotência e resiliência

**Decisão:** O job `AplicarCreditoPendente` usa `Cache::lock` com chave baseada no `cliente_id` para garantir idempotência, e backoff exponencial de `[60s, 5min, 30min]` para resiliência a falhas.

**Justificativa:** O `ShouldBeUnique` do Laravel foi descartado porque protege apenas a fila, não a execução simultânea — dois workers poderiam processar o mesmo cliente ao mesmo tempo e aplicar crédito em dobro. O `Cache::lock` resolve isso na camada de execução. O backoff exponencial cobre falhas transitórias de banco sem sobrecarregar o sistema durante instabilidades.

---

## 5. Performance: dashboard, paginação e rate limiting

**Decisão:** O dashboard consolida todos os dados em 4 queries otimizadas com `CASE WHEN`, sem N+1, com cache de 5 minutos invalidado automaticamente via evento `CobrancaStatusAlterado`. A listagem de cobranças usa paginação por cursor (`cursorPaginate`) como padrão, com fallback para offset quando `?page=N` é enviado. O rate limit de 20 req/min na listagem usa o limitador nativo do Laravel via `RateLimiter::for()`, por usuário autenticado.

**Justificativa:** Paginação por cursor evita o `OFFSET` custoso em tabelas grandes — relevante para históricos longos de cobranças. O cache do dashboard com invalidação por evento garante dados frescos sem TTL agressivo. O rate limit por `user_id` em vez de IP evita que usuários do mesmo proxy corporativo se bloqueiem mutuamente.
