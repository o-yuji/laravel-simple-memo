@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            メモ編集
            <form class="card-body" action="{{ route('destory') }}" method="POST">
                @csrf
                <input type="hidden" name="memo_id" value="{{ $edit_memo[0]['id'] }}">
                <button type="submit">削除</button>
            </form>
        </div>
        <form class="card-body my-card-body" action="{{ route('update') }}" method="POST">
            @csrf
            <input type="hidden" name="memo_id" value="{{$edit_memo[0]['id']}}">
            <div class="form-group">
                <textarea class="form-control" name="content" rows="3" placeholder="ここにメモを入力">{{ $edit_memo[0]['content'] }}</textarea>
                @foreach($tags as $tag)
                    <input type="checkbox" name="tags[]" id="{{ $tag['id'] }}" value="{{ $tag['id'] }}"
                     {{ in_array($tag['id'],$include_tags) ? 'checked':'' }}>
                    <label class="me-3 form-check-label" for="{{ $tag['id'] }}">{{ $tag['name'] }}</label>
                @endforeach
                <input type="text" class="mt-3 form-control w-50" name="new_tag" placeholder="新しいタグを入力">
                <button type="submit" class="mt-3 btn btn-primary">更新</button>
            </div>
        </form>
    </div>

@endsection
