@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Process List') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <table class="table table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Host</th>
                                    <th scope="col">DB</th>
                                    <th scope="col">Command</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">State</th>
                                    <th scope="col">Info</th>
                                    <th scope="col">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php($count = 0)
                                @foreach($processList as $processItem)
                                    @php($count++)
                                    <tr>
                                        <th scope="row">{{ $count }}</th>
                                        <td>{{ $processItem->User }}</td>
                                        <td>{{ $processItem->Host }}</td>
                                        <td>{{ $processItem->db }}</td>
                                        <td>{{ $processItem->Command }}</td>
                                        <td>{{ $processItem->Time }}</td>
                                        <td>{{ $processItem->State }}</td>
                                        <td>{{ $processItem->Info }}</td>
                                        <td>{{ $processItem->Progress }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
