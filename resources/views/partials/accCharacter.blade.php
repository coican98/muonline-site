@if (isset($results))
<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>GameID1</th>
            <th>GameID2</th>
            <th>GameID3</th>
            <th>GameID4</th>
            <th>GameID5</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results['success'] as $successRow)
            <tr>
                <td>{{ $successRow['Id'] }}</td>
                <td>{{ $successRow['GameID1'] }}</td>
                <td>{{ $successRow['GameID2'] }}</td>
                <td>{{ $successRow['GameID3'] }}</td>
                <td>{{ $successRow['GameID4'] }}</td>
                <td>{{ $successRow['GameID5'] }}</td>
            </tr>
        @endforeach

        @foreach($results['errors'] as $errorRow)
            <tr>
                <td>{{ $errorRow['sql']['Id'] }}</td>
                <td>
                    <span style="color: green;">{{ $errorRow['sql']['GameID1'] }}</span><br>
                    @if($errorRow['csv'])
                        <span style="color: red;">{{ $errorRow['csv']['GameID1'] }}</span>
                    @endif
                </td>
                <td>
                    <span style="color: green;">{{ $errorRow['sql']['GameID2'] }}</span><br>
                    @if(isset($errorRow['csv']))
                    <span style="color: red;">{{ $errorRow['csv']['GameID2'] }}</span>
                    @endif
                </td>
                <td>
                    <span style="color: green;">{{ $errorRow['sql']['GameID3'] }}</span><br>
                    @if(isset($errorRow['csv']))
                    <span style="color: red;">{{ $errorRow['csv']['GameID3'] }}</span>
                    @endif
                </td>
                <td>
                    <span style="color: green;">{{ $errorRow['sql']['GameID4'] }}</span><br>
                    @if(isset($errorRow['csv']))
                    <span style="color: red;">{{ $errorRow['csv']['GameID4'] }}</span>
                    @endif
                </td>
                <td>
                    <span style="color: green;">{{ $errorRow['sql']['GameID5'] }}</span><br>
                    @if(isset($errorRow['csv']))
                    <span style="color: red;">{{ $errorRow['csv']['GameID5'] }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif