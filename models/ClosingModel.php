<?php
class ClosingModel {

    private $db;

    public function __construct() {
        require 'db.php';
        $this->db = $pdo;
    }

    public function closePeriod($period) {

        // 1. Close Revenues → Income Summary
        $this->db->exec("
            INSERT INTO closing_entries (period, entry_type, description, debit, credit)
            SELECT '$period','close','Close revenue',0, SUM(amount_paid)
            FROM account_general_transaction_new
            WHERE credit_account IN (SELECT acct_id FROM accounts WHERE acct_class_type='Revenue')
        ");

        // 2. Close Expenses → Income Summary
        $this->db->exec("
            INSERT INTO closing_entries (period, entry_type, description, debit, credit)
            SELECT '$period','close','Close expenses', SUM(amount_paid), 0
            FROM account_general_transaction_new
            WHERE debit_account IN (SELECT acct_id FROM accounts WHERE acct_class_type='Expense')
        ");

        // 3. Transfer Income Summary → Retained Earnings
        $this->db->exec("
            INSERT INTO closing_entries (period, entry_type, description, debit, credit)
            VALUES ('$period','close','Transfer to Retained Earnings',0,0)
        ");

        // 4. Create Opening Entries for next period
        $this->db->exec("
            INSERT INTO opening_balances (acct_id, period, opening_debit, opening_credit)
            SELECT
                a.acct_id,
                '$period',
                SUM(CASE WHEN ag.debit_account=a.acct_id THEN ag.amount_paid ELSE 0 END),
                SUM(CASE WHEN ag.credit_account=a.acct_id THEN ag.amount_paid ELSE 0 END)
            FROM accounts a
            LEFT JOIN account_general_transaction_new ag
                 ON ag.debit_account=a.acct_id OR ag.credit_account=a.acct_id
            GROUP BY a.acct_id
        ");

        return true;
    }
}
?>