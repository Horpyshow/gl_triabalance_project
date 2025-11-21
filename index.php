<?php
ob_start();
$action = isset($_GET['action']) ? $_GET['action'] : 'gl';

switch ($action) {

    // General Ledger
    case 'gl':
        require 'controllers/LedgerController.php';
        (new LedgerController())->index();
        break;

    // Ledger for individual account
    case 'ledger':
        require 'controllers/LedgerController.php';
        (new LedgerController())->account();
        break;

    // Trial Balance
    case 'trial-balance':
        require 'controllers/TrialBalanceController.php';
        (new TrialBalanceController())->index();
        break;

    // IFRS Statements (4 pages inside)
    case 'ifrs':
        require 'controllers/FinancialStatementsController.php';
        (new FinancialStatementsController())->index();
        break;

    // NEW: Closing & Opening Entries UI
    case 'closing':
        require 'controllers/ClosingController.php';
        (new ClosingController())->index();
        break;

    // NEW: Execute closing posting → opening balances
    case 'run-closing':
        require 'controllers/ClosingController.php';
        (new ClosingController())->runClosing();
        break;
}
$content = ob_get_clean(); 
require 'admin/'; // wrap page inside UI