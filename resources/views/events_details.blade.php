@extends('layouts.main')

@section('content')
<div class="section-heading">
    <h2>Eventos e Invasões</h2>
    <span>Confira o cronograma completo das Invasões de Bosses e Eventos Diários</span>
</div>

<div style="overflow-x: auto; margin-top: 20px;">
    <table class="downloads-table">
        <thead>
            <tr>
                <th>Evento / Invasão</th>
                <th>Dia da Semana</th>
                <th>Horário do Respawn</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td style="color: var(--gold-bright); font-weight: bold; font-family: var(--font-display); letter-spacing: 1px;">
                        {{ $event['name'] }}
                    </td>
                    <td style="color: var(--steel);">
                        @if(isset($event['timestamp']['dow']) && $event['timestamp']['dow'] === '*')
                            Todos os Dias
                        @else
                            {{ $event['timestamp']['dow'] ?? 'Não especificado' }}
                        @endif
                    </td>
                    <td style="line-height: 1.8;">
                        @if($event['timestamp']['hour'] === '*')
                            @php
                                $minute = str_pad($event['timestamp']['minute'], 2, '0', STR_PAD_LEFT);
                                $allHours = [];
                                for ($h = 0; $h < 24; $h++) {
                                    $allHours[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $minute;
                                }
                            @endphp
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach($allHours as $fullHour)
                                    <span style="background: rgba(201, 166, 90, 0.15); border: 1px solid rgba(201,166,90, 0.4); border-radius: 4px; padding: 2px 7px; font-weight: bold; color: var(--gold-bright); font-size: 13px; font-family: monospace;">
                                        {{ $fullHour }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="background: rgba(201, 166, 90, 0.15); border: 1px solid rgba(201,166,90, 0.4); border-radius: 4px; padding: 4px 8px; font-weight: bold; color: var(--gold-bright); font-size: 14px; font-family: monospace;">
                                {{ str_pad($event['timestamp']['hour'], 2, '0', STR_PAD_LEFT) }}:{{ str_pad($event['timestamp']['minute'], 2, '0', STR_PAD_LEFT) }}
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Nenhum evento agendado no momento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
