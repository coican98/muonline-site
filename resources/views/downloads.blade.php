@extends('layouts.main')
@section('content')
<table class="downloads-table">
    <thead>
        <tr>
            <th>File Name</th>
            <th>Download Links</th>
            <th>Size</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($downloads as $download)
        <tr>
            <td>{{ $download['name'] }}</td>
            <td><a href="{{ $download['link'] }}">Download Link</a></td>
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