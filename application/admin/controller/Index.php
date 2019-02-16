<?php


namespace app\admin\controller;

use think\Controller;


class Index extends Controller
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     *                    .::::.
     *                  .::::::::.
     *                 :::::::::::  
     *             ..:::::::::::'
     *           '::::::::::::'
     *             .::::::::::
     *        '::::::::::::::..
     *             ..::::::::::::.
     *           ``::::::::::::::::
     *            ::::``:::::::::'        .:::.
     *           ::::'   ':::::'       .::::::::.
     *         .::::'      ::::     .:::::::'::::.
     *        .:::'       :::::  .:::::::::' ':::::.
     *       .::'        :::::.:::::::::'      ':::::.
     *      .::'         ::::::::::::::'         ``::::.
     *  ...:::           ::::::::::::'              ``::.
     * ```` ':.          ':::::::::'                  ::::..
     *                    '.:::::'                    ':'````..
     */
    public function index()
    {
        $url='http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        return redirect($url.'houtai/','',302);
    }
}