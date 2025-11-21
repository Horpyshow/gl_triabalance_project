<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/TrialBalanceModel.php';

class TrialBalanceController extends BaseController {

    public function index() {
        $model = new TrialBalanceModel();
        $rows = $model->getTrialBalance();

        $this->view('trial_balance', array(
            'rows' => $rows
        ));
    }
}
?>
