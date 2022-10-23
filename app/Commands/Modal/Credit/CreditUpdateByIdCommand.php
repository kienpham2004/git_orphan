<?php

namespace App\Commands\Modal\Credit;

use App\Models\Credit;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;
use Session;

/**
 * 車点検内容更新コマンド
 *
 * @author yhatsutori
 */
class CreditUpdateByIdCommand extends Command{

    protected $id;
    protected $requestObj;

    /**
     * コンストラクタ
     *
     * @param $id 活動内容のID
     * @param ActionListRequest $requestObj 更新内容
     */
    public function __construct( $id, SearchRequest $requestObj ){
        $this->id = $id;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return 活動内容
     */
    public function handle(){
        // IDで活動内容を取得する
        $tgc = Credit::findOrFail( $this->id );
        
        // 更新対象のカラムを定義
        $columns = [
            'cre_status', // 意向結果
            'cre_action', // 活動内容
            'cre_satei_flg', // 活動内容
            'cre_memo', // メモ
        ];
        
        /**
         * 更新用に値を詰め込む
         */
        foreach( $columns as $column ){
            $tgc->{$column} = $this->requestObj->{$column};
        }

        // 更新する
        $tgc->save();

        // 更新のセッションを用意
        Session::put('update', 1);

        return $tgc;
    }
}
