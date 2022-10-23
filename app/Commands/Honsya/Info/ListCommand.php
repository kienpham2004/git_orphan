<?php

namespace App\Commands\Honsya\Info;

use App\Models\Info;
use App\Commands\Command;

/**
 * お知らせ一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{

    /**
     * コンストラクタ
     * @param [type]  $sort       [description]
     * @param [type]  $requestObj [description]
     */
    public function __construct( $sort, $requestObj ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            'tb_info.*' => 'ALL',
            'tb_user_account.user_name' => '担当者'
        ];
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){

        // 他のテーブルとJOIN
        $builderObj = Info::joinUser();

        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj );
        
        //dd($builderObj -> toSql());
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );

        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');

        return $data;
    }

}
