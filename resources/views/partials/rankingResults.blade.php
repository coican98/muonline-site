@if (isset($rankingData))
<div>
    <table class="ranking-list">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Pontuação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rankingData as $ranking)
                <tr class="ranking-item">
                    <td><span class="ranking-item-name">{{$ranking->Name}} </span></td>
                    <td><span class="ranking-item-score">{{$ranking->Score}} </span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif