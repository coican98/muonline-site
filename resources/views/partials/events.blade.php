@if(isset($eventData))
<div class="event-list">
    <h3>Eventos</h3>
    <table class="event-schedule-table">
        <thead>
            <tr>
                <th>Evento</th>
                <th>Horário</th>
                <th>Restante</th>
                @auth
                @if(Auth::user()->global_admin == 1)
                    <th>Status</th>
                @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @foreach($eventData as $event)
                @if((Auth::check() && Auth::user()->global_admin == 1))
                    <tr class="event-item">
                @elseif(($event['enabled'] && $event['time'] !== null))
                    <tr class="event-item">
                @else
                    <tr class="event-item" hidden>
                @endif
                <td><span class="event-name">{{ $event['event'] }}</span></td>
                <td><span class="event-timestamp" data-hour="{{ $event['time'] }}" id="timestamp-{{ $loop->index }}">
                    @if($event['time'] == null)
                    N/A
                    @else
                    @if($event['dow'] != '*')
                    {{ $event['dow']}}
                    @endif
                    {{ $event['time'] }}
                    @endif
                </span></td>
                <td><span class="event-remaining" id="remaining-{{ $loop->index }}">N/A</span></td>
                
                @if(Auth::check() && Auth::user()->global_admin == 1)
                    <td>
                        @if($event['enabled'] && $event['time'] !== null)
                            <span class="badge badge-success">✓</span>
                        @else
                            <span class="badge badge-danger">✕</span>
                        @endif
                    </td>
                @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif