<?php

namespace App\Commands\Honsya\Base;

use App\Models\Base;
use App\Commands\Command;

/**
 * 拠点一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{

    /**
     * コンストラクタ
     * @param [type] $sort       [description]
     * @param [type] $requestObj [description]
     */
    public function __construct( $sort, $requestObj ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 検索条件を指定
        $builderObj = Base::whereRequest( $this->requestObj );
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );

        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num )
            // 表示URLをpagerに指定
            ->setPath('pager');

        return $data;
    }
}
