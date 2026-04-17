<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Cobranca;
use App\Models\OrdemServico;
use App\Models\User;
use App\Domain\Cobranca\StatusCobranca;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $operacional = User::where('role', 'operacional')->first();

        $clientes = [
            [
                'nome'      => 'Mercurio Materiais de Construção LTDA',
                'documento' => '12.345.678/0001-99',
                'email'     => 'contato-mercurio@somosdapaz.com.br',
                'telefone'  => '(84) 99999-1001',
            ],
            [
                'nome'      => 'Venus Soluções S/A',
                'documento' => '98.765.432/0001-11',
                'email'     => 'financeiro-venus@venussolucoes.com.br',
                'telefone'  => '(84) 99999-1002',
            ],
            [
                'nome'      => 'Terra Comércio ME',
                'documento' => '11.222.333/0001-44',
                'email'     => 'contato-terra@terra.com.br',
                'telefone'  => '(84) 99999-1003',
            ],
            [
                'nome'      => 'Marte Serviços EIRELI',
                'documento' => '55.666.777/0001-88',
                'email'     => 'marte-contato@servicosemmarte.com',
                'telefone'  => '(84) 99999-1004',
            ],
            [
                'nome'      => 'Júpiter Tecnologia',
                'documento' => '22.333.444/0001-55',
                'email'     => 'ti@jupiter.tech',
                'telefone'  => '(84) 99999-1005',
            ],
        ];

        foreach ($clientes as $i => $dadosCliente) {
            $cliente = Cliente::updateOrCreate(
                ['documento' => $dadosCliente['documento']],
                array_merge($dadosCliente, ['saldo_credito' => $i === 0 ? 500.00 : 0])
            );

            // Cria um contrato ativo para cada cliente
            $contrato = Contrato::create([
                'cliente_id'     => $cliente->id,
                'descricao'      => "Contrato de Serviços Digitais - {$cliente->nome}",
                'data_inicio'    => Carbon::now()->subMonths(6)->toDateString(),
                'status'         => 'ativo',
                'dia_vencimento' => 10,
            ]);

            // Itens do contrato
            $itens = [
                ['descricao' => 'Gestão de tráfego', 'quantidade' => 1, 'valor_unitario' => 1500.00],
                ['descricao' => 'Criação de conteúdo', 'quantidade' => 4, 'valor_unitario' => 300.00],
            ];
            if ($i % 2 === 0) {
                $itens[] = ['descricao' => 'Manutenção de site', 'quantidade' => 1, 'valor_unitario' => 800.00];
            }
            foreach ($itens as $item) {
                $contrato->itens()->create($item);
            }

            $valorCobranca = $contrato->itens->sum(fn ($it) => $it->quantidade * $it->valor_unitario);

            // Gera cobranças dos últimos 3 meses
            for ($m = 3; $m >= 1; $m--) {
                $referencia   = Carbon::now()->subMonths($m)->startOfMonth();
                $vencimento   = $referencia->copy()->day(10);
                $status       = $m > 1 ? StatusCobranca::PAGO : StatusCobranca::AGUARDANDO_PAGAMENTO;

                Cobranca::create([
                    'contrato_id'     => $contrato->id,
                    'cliente_id'      => $cliente->id,
                    'valor'           => $valorCobranca,
                    'valor_pago'      => $status === StatusCobranca::PAGO ? $valorCobranca : 0,
                    'credito_aplicado'=> 0,
                    'status'          => $status,
                    'data_referencia' => $referencia->toDateString(),
                    'data_vencimento' => $vencimento->toDateString(),
                ]);
            }

            // Cria uma OS para o contrato
            if ($operacional) {
                OrdemServico::create([
                    'contrato_id'    => $contrato->id,
                    'responsavel_id' => $operacional->id,
                    'titulo'         => "Entrega mensal - {$cliente->nome}",
                    'descricao'      => 'Produção e publicação de conteúdos mensais.',
                    'status'         => 'em_andamento',
                    'horas_estimadas'  => 20,
                    'horas_realizadas' => 12,
                ]);
            }
        }

        $this->command->info('5 clientes criados com contratos, cobranças e ordens de serviço.');
    }
}
