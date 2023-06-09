@extends('layouts.app')

@section('content')

    <div class="card">
        <h5 class="card-header">新規メモ作成</h5>
        <form class="card-body my-card-body" action="{{ route('store') }}" method="POST">
            @csrf
            <div class="form-group ">
                <textarea class="form-control" name="content" rows="3" placeholder="ここにメモを入力"></textarea>
            </div>

            @foreach($tags as $tag)
                <input type="checkbox" name="tags[]" id="{{ $tag['id'] }}" value="{{ $tag['id'] }}">
                <label class="me-3 form-check-label" for="{{ $tag['id'] }}">{{ $tag['name'] }}</label>
            @endforeach

            <input type="text" class="mt-3 form-control w-50" name="new_tag" placeholder="新しいタグを入力">
            <button type="submit" class="mt-3 btn btn-primary">保存</button>

        </form>
    </div>

@endsection
