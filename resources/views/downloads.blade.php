@extends('layouts.main')
@section('content')
<table class="downloads-table">
    <thead>
        <tr>
            <th>Nome Arquivo</th>
            <th>Link</th>
            <th>Tamanho do arquivo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($downloads as $download)
        <tr>
            <td>{{ $download['name'] }}</td>
            <td><a href="{{ $download['link'] }}">
                @if(str_starts_with($download['link'], env('APP_URL')))
                    Download Direto
                @else
                    Download Externo
                @endif
                </a></td>
            <td>{{ $download['size'] }}</td>
            @if(Auth::check())
                @if(Auth::user()->global_admin == 1)
                    <td><a href="{{ route('removeDownloadFile', ['download' => $download['name']]) }}" class="btn btn-danger">Delete</a></td>
                @endif
            @endif
        </tr>
        @endforeach
    </tbody>
</table>


@endsection