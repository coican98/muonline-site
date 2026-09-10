@extends('layouts.main')
@section('content')
@if ($errors != null)
    <small class="text-danger">{{$errors}}</small>
@endif

<form action="{{ route('upload')}}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file"><p></p>
    <button type="submit">Upload</button>
</form>

<section class="admin-action-panel">
    <h2>Desconectar jogador</h2>
    <form action="{{ route('admin.players.disconnect') }}" method="POST">
        @csrf
        <input type="text" name="username" placeholder="Conta do jogador" maxlength="20" required>
        <button type="submit">Desconectar</button>
    </form>
</section>

@if (isset($csvData))
<table class="comparison-table">
    <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($sqlData as $sqlRow)
            <tr>
                @php
                    $csvRow = collect($csvData)->firstWhere('Id', $sqlRow->Id);
                @endphp

                @foreach ($columns as $column)
                    <td>
                        @if ($csvRow && isset($csvRow[$column]) && $csvRow[$column] != $sqlRow->{$column})
                            <span style="color: green;">{{ $sqlRow->{$column} }}</span><br>
                            <span style="color: red;">{{ $csvRow[$column] }}</span>
                        @else
                            @if ($sqlRow->{$column} === $sqlRow->Id)
                                <span style="color: lightgray;">{{ $sqlRow->{$column} }}</span>
                            @else
                                <span style="color: green;">{{ $sqlRow->{$column} }}</span>
                            @endif
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
@endif





@endsection