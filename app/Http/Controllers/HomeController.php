<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //ここでメモを取得
        $memos = Memo::select('memos.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at','DESC')
            ->get();
            // dd($memos);
        $tags = Tag::where('user_id','=',\Auth::id())->whereNull('deleted_at')->orderBy('id','DESC')->get();

        return view('create',compact('memos','tags'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();
        //dump dieの略　→　メソッドの引数の取った値を展開して止める　データの確認
        // dd($posts);

        //トランザクション開始
        DB::transaction(function() use($posts) {
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id'=> \Auth::id()]);
            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name','=',$posts['new_tag'])
            ->exists();
            //新規タグが入力されているかチェック
            //新規タグがtagsテーブルに存在するかチェック
            if(!empty($posts['new_tag']) && !$tag_exists){
                //新規タグが存在しなければ、tagsテーブルにインサート→IDを取得
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(),'name' => $posts['new_tag']]);
                //memo_tagsにインサートして、メモとタグを紐づける
                MemoTag::insert(['memo_id'=>$memo_id,'tag_id'=>$tag_id]);
            }
            //既存タグが紐づけされた場合
            if(!empty($posts['tags'][0])){
                foreach($posts['tags'] as $tag){
                    MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
                }
            }
        });
        // dd($posts);
        //トランザクションここまで

        return redirect( route('home') );
    }

    public function edit($id)
    {
        //ここでメモを取得
        $memos = Memo::select('memos.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at','DESC')
            ->get();
            // dd($memos);

        $edit_memo = Memo::select('memos.*','tags.id AS tag_id')
            ->leftJoin('memo_tags','memo_tags.memo_id','=','memos.id')
            ->leftJoin('tags','memo_tags.tag_id','=','tags.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->where('memos.id', '=', $id)
            ->whereNull('memos.deleted_at')
            ->get();

        $include_tags = [];
        foreach($edit_memo as $memo){
            array_push($include_tags,$memo['tag_id']);
        }

        $tags = Tag::where('user_id','=',\Auth::id())->whereNull('deleted_at')->orderBy('id','DESC')->get();

        return view('edit',compact('memos','edit_memo','include_tags','tags'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        //dump dieの略　→　メソッドの引数の取った値を展開して止める　データの確認
        // dd($posts);

        //トランザクションスタート
        DB::transaction(function() use($posts){
            Memo::where('id',$posts['memo_id'])->update(['content' => $posts['content']]);
            //一旦メモとタグの紐づけを解除
            MemoTag::where('memo_id','=',$posts['memo_id'])->delete();
            //再度メモとタグの紐づけ
            foreach($posts['tags'] as $tag):
                Memotag::insert(['memo_id' => $posts['memo_id'],'tag_id'=>$tag ]);
            endforeach;
            //新しいタグの入力があれば、インサートして紐づけ
            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name','=',$posts['new_tag'])
            ->exists();
            if(!empty($posts['new_tag']) && !$tag_exists){
                //新規タグが存在しなければ、tagsテーブルにインサート→IDを取得
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(),'name' => $posts['new_tag']]);
                //memo_tagsにインサートして、メモとタグを紐づける
                MemoTag::insert(['memo_id'=> $posts['memo_id'],'tag_id'=>$tag_id]);
            }
        });
        //トランザクションここまで




        return redirect( route('home') );
    }

    public function destory(Request $request)
    {
        $posts = $request->all();
        //dump dieの略　→　メソッドの引数の取った値を展開して止める　データの確認
        // dd($posts);

        Memo::where('id',$posts['memo_id'])->update(['deleted_at' => date("Y-m-d H:i:s",time())]);
        return redirect( route('home') );
    }

}
