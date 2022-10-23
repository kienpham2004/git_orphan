<?php

namespace App\Commands\Modal\Syatenken;

use App\Models\Targetcars;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;

use App\Models\Views\VContactComment5;

/**
 * 指定IDの車点検内容取得コマンド
 *
 * @author yhatsutori
 */
class SyatenkenCommentFindByIdCommand extends Command{

    /** @var integer 検索対象のid */
    protected $id;

    /** @var array 取得カラム */
    protected $columns = [
        "id",
        "tgc_customer_code"
    ];

    public function __construct( $id ){
        $this->id = $id;
    }

    public function handle(){
        // コメント検索用に顧客コードを取得
        $customerCodeObj = TargetCars::find( $this->id, $this->columns );
        // 最新5件のコメントを取得
        $commentObj = VContactComment5::findTarget( $customerCodeObj->tgc_customer_code );

        return $commentObj;
    }

}
