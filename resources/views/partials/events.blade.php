@if(isset($eventData))
<div class="event-list" style="margin-top: 15px;">
    <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 1.15em; border-bottom: 1px solid rgba(201,166,90,0.4); padding-bottom: 6px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1.5px; text-shadow: 1px 1px 3px black;">Eventos</h3>
    <table class="ranking-list" style="margin: 0; width: 100%; border: none; box-shadow: none; background: transparent;">
        @foreach($eventData as $event)
            @php
                $isAdmin = Auth::check() && Auth::user()->global_admin == 1;
                $isVisible = $isAdmin || ($event['enabled'] && $event['time'] !== null);
            @endphp
            <tr class="event-item" data-enabled="{{ $event['enabled'] && $event['time'] !== null ? '1' : '0' }}" {!! !$isVisible ? 'style="display:none;"' : '' !!}>
                <td style="padding: 7px 4px; border: none; border-bottom: 1px solid rgba(201,166,90,0.15); background: transparent;">
                    <span class="event-name" style="color: var(--gold-bright); font-weight: 700; font-family: var(--font-display); font-size: 12.5px; letter-spacing: 0.8px; text-shadow: 1px 1px 2px black;">{{ $event['event'] }}</span>
                </td>
                <td style="padding: 7px 4px; border: none; border-bottom: 1px solid rgba(201,166,90,0.15); text-align: right; line-height: 1.3; background: transparent;">
                    <span class="event-timestamp" data-hour="{{ $event['time'] }}" data-dow="{{ $event['dow'] ?? '*' }}" id="timestamp-{{ $loop->index }}" style="color: var(--steel); font-family: var(--font-display); font-size: 12px; font-weight: 600;">
                        @if($event['time'] == null)
                            N/A
                        @else
                            {{ $event['time'] }}
                        @endif
                    </span><br>
                    <span class="event-remaining" id="remaining-{{ $loop->index }}">N/A</span>
                    @if($isAdmin)
                        @if($event['enabled'] && $event['time'] !== null)
                            <span class="event-status-dot" style="color: #4ade80; font-size: 10px; margin-left: 3px;" title="Ativo">●</span>
                        @else
                            <span class="event-status-dot" style="color: var(--blood-bright); font-size: 10px; margin-left: 3px;" title="Inativo">●</span>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endif