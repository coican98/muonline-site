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
                @if(!empty($isAdmin))
                    <th style="text-align: center;">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td style="color: var(--gold-bright); font-weight: bold; font-family: var(--font-display); letter-spacing: 1px;">
                        {{ $event['name'] }}
                    </td>
                    <td style="color: var(--steel);">
                        @php
                            $dowVal = $event['dow'] ?? ($event['timestamp']['dow'] ?? '*');
                        @endphp
                        @if($dowVal === '*' || $dowVal === '-1' || empty($dowVal))
                            Todos os Dias
                        @else
                            {{ $dowVal }}
                        @endif
                    </td>
                    <td style="line-height: 1.8;">
                        @if(!empty($event['all_hours_sorted']))
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach($event['all_hours_sorted'] as $hStr)
                                    <span style="background: rgba(201, 166, 90, 0.15); border: 1px solid rgba(201,166,90, 0.4); border-radius: 4px; padding: 3px 8px; font-weight: bold; color: var(--gold-bright); font-size: 13.5px; font-family: monospace;">
                                        {{ $hStr }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif(!empty($event['all_hours']))
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach($event['all_hours'] as $hStr)
                                    <span style="background: rgba(201, 166, 90, 0.15); border: 1px solid rgba(201,166,90, 0.4); border-radius: 4px; padding: 3px 8px; font-weight: bold; color: var(--gold-bright); font-size: 13.5px; font-family: monospace;">
                                        {{ $hStr }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif(isset($event['timestamp']['hour']))
                            <span style="background: rgba(201, 166, 90, 0.15); border: 1px solid rgba(201,166,90, 0.4); border-radius: 4px; padding: 4px 8px; font-weight: bold; color: var(--gold-bright); font-size: 14px; font-family: monospace;">
                                {{ str_pad($event['timestamp']['hour'], 2, '0', STR_PAD_LEFT) }}:{{ str_pad($event['timestamp']['minute'] ?? '00', 2, '0', STR_PAD_LEFT) }}
                            </span>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px; font-style: italic;">
                                Horário dinâmico / Contínuo
                            </span>
                        @endif
                    </td>
                    @if(!empty($isAdmin))
                        <td style="text-align: center;">
                            @if(!empty($event['enabled']))
                                <span style="background: rgba(20, 83, 45, 0.6); border: 1px solid #22c55e; color: #86efac; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                    Ativo
                                </span>
                            @else
                                <span style="background: rgba(127, 29, 29, 0.6); border: 1px solid #ef4444; color: #fca5a5; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                    Inativo
                                </span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ !empty($isAdmin) ? 4 : 3 }}" style="text-align: center;">Nenhum evento agendado no momento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
