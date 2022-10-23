<?php

namespace App\Original\Codes;

use App\Lib\Codes\Code;

//use App\Models\Action;
use App\Models\Append\DaigaeSyaken;
use App\Models\Append\Htc;
use App\Models\Append\Recall;
use App\Models\Customer;
use App\Models\Append\Abc;
use App\Models\Append\Ciao;
use App\Models\Append\Tmr;
use App\Models\Append\Pit;
use App\Models\Append\SmartPro;
use App\Models\Append\Ikou;
use App\Models\Contact\Contact;
use App\Models\Insurance;
use App\Models\SyakenJisshi;

/**
 * Csvファイルと紐づく、DBモデルを保持するコード
 *
 * @author yhatsutori
 *
 */
class CsvModelCountCodes extends Code {
        
    private $codes = [];
        
    /**
     * コンストラクタ
     */
    public function __construct() {
        //$this->codes[1] = Action::count();
        //$this->codes[2] = Action::count();

        $this->codes[3] = Customer::count();
        $this->codes[4] = Tmr::count();
        $this->codes[5] = Pit::count();

        //$this->codes[6] = Abc::count();
        //$this->codes[7] = Ciao::count();

        $this->codes[8] = Contact::count();
        $this->codes[9] = SmartPro::count();
        $this->codes[10] = Ikou::count();
        $this->codes[12] = Recall::count();
        $this->codes[13] = Htc::count();
        $this->codes[14] = SyakenJisshi::count();
        $this->codes[15] = DaigaeSyaken::count();
        $this->codes[20] = Insurance::count();

        //12 => 'credit',
        //13 => 'shodan',
        //14 => 'daigae',
        //15 => 'ikou_syatenken',

        parent::__construct( $this->codes );
    }

}
