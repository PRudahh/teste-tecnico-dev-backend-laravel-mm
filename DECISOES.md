# DECISOES.md — Decisões Técnicas

Este arquivo documenta todas as decisões tomadas durante o desenvolvimento, incluindo ambiguidades identificadas no enunciado e as justificativas para cada escolha.

---

## 1. Autenticação: Laravel Sanctum

**Decisão:** Usar Laravel Sanctum em vez de JWT.

**Justificativa:**
- Sanctum é nativo do Laravel, sem dependências externas.
- Para APIs internas (sistema de agência, usuários fixos), tokens stateful armazenados no banco são mais simples de revogar e auditar.
- JWT exigiria dependência externa (`tymon/jwt-auth`) e lógica adicional de refresh token.
- Sanctum oferece revogação granular por token, útil para ambientes com múltiplos dispositivos.

---

## 2. Data de vencimento das cobranças (ambiguidade do enunciado)

**Ambiguidade identificada:** O enunciado não especifica onde ou como a data de vencimento de uma cobrança é definida.

**Decisão:** A cobrança tem um campo `data_vencimento` calculado automaticamente no momento de sua criação como **o dia 10 do mês de referência**. Exemplo: cobrança de referência maio/2025 vence em 10/05/2025.

**Justificativa:**
- Dia 10 é uma convenção comum em contratos de serviços recorrentes no mercado brasileiro.
- Mantém o vencimento previsível e calculável sem necessidade de input manual.
- A data é armazenada explicitamente na cobrança para permitir filtros e ordenação eficientes.

---

## 3. Valor total do contrato

**Decisão:** O valor total do contrato **não é armazenado** na tabela `contratos`. Ele é sempre calculado dinamicamente a partir da soma dos itens (`quantidade * valor_unitario`).

**Justificativa:** O enunciado é explícito nesse ponto ("nunca salvo de forma redundante"). Usamos um accessor `getValorTotalAttribute()` no Model e um scope para queries que precisam do valor calculado via SQL (`SUM(quantidade * valor_unitario)`).

---

## 4. Paginação das cobranças (ambiguidade do enunciado)

**Ambiguidade identificada:** O enunciado diz "paginação (escolha a melhor abordagem)" sem especificar qual.

**Decisão:** Usar **paginação por cursor** (`cursorPaginate`) como padrão, com fallback para paginação por offset quando o usuário precisar de total de páginas.

**Justificativa:**
- Paginação por cursor é mais eficiente em tabelas grandes (evita `OFFSET` custoso).
- Para cobranças de longo histórico, a diferença de performance é significativa.
- A resposta inclui `next_cursor` para navegação sequencial.
- Quando o parâmetro `?page=N` for enviado, usa `paginate()` tradicional com total de registros.

---

## 5. Idempotência do Job `AplicarCreditoPendente`

**Decisão:** Usar **lock distribuído via banco de dados** (`Cache::lock`) com chave baseada no `cliente_id`.

**Implementação:**
```php
$lock = Cache::lock("aplicar_credito_{$this->clienteId}", 60);
if (!$lock->get()) {
    // Outro job está processando este cliente, aborta silenciosamente
    return;
}
```

**Justificativa:**
- Garante que apenas uma instância do job processe um cliente por vez.
- Se dois jobs forem disparados simultaneamente para o mesmo cliente, o segundo obtém o lock apenas após o primeiro terminar (ou não obtém e encerra silenciosamente).
- Usar cache com driver `database` ou `redis` garante atomicidade mesmo em múltiplos workers.
- Alternativa considerada: unique jobs do Laravel (`ShouldBeUnique`) — descartada porque não cobre o caso de job já sendo *processado* (só previne duplicatas na fila, não durante execução).

---

## 6. Cache do Dashboard

**Decisão:** Cache com TTL de 5 minutos, invalidado via `Cache::tags(['dashboard'])` sempre que uma cobrança mudar de status.

**Implementação:** O evento `CobrancaStatusAlterado` dispara um listener que chama `Cache::tags(['dashboard'])->flush()`.

**Nota:** Para usar tags de cache, o driver deve ser `redis` ou `memcached`. No `.env.example` está configurado `redis`. Se usar `file` ou `database`, tags não funcionam — nesse caso, a chave plana `dashboard_financeiro` é invalidada diretamente.

---

## 7. Transições de status das cobranças

**Fluxo definido:**

```
pendente → aguardando_pagamento → pago
                              → pago_parcial → pago
                              → inadimplente → pago
                              → cancelado
         → cancelado
```

**Regras implementadas como Value Object `StatusCobranca`** com método `podeMudarPara(string $novoStatus): bool` para encapsular a lógica de transição e lançar `DomainException` em transições inválidas.

---

## 8. Aplicação de crédito: ordem de prioridade

**Ambiguidade identificada:** O enunciado diz "aplica na mais antiga primeiro" mas não define como desempatar cobranças com mesma data.

**Decisão:** Ordenar por `data_vencimento ASC`, com desempate por `id ASC`.

---

## 9. Role/permissões de usuários

**Ambiguidade identificada:** O enunciado menciona "usuários com role financeiro" mas não especifica um sistema de permissões.

**Decisão:** Implementar sistema simples com campo `role` na tabela `users` (enum: `admin`, `financeiro`, `operacional`) e middleware `CheckRole` para proteger rotas específicas.

**Justificativa:** Spatie/laravel-permission seria overkill para o escopo do teste. A solução simples com enum é suficiente e fácil de entender e manter.

---

## 10. Rate Limiting

**Decisão:** Usar o rate limiter nativo do Laravel definido em `RouteServiceProvider` via `RateLimiter::for()`.

```php
RateLimiter::for('api-cobrancas', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
});
```

Aplicado à rota com `->middleware('throttle:api-cobrancas')`.