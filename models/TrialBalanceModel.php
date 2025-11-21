<?php
require_once 'BaseModel.php';

class TrialBalanceModel extends BaseModel {

    public function getTrialBalance() {
        $sql = "
            SELECT 
                a.acct_id,
                a.acct_code,
                a.acct_alias,
                a.acct_class_type,
                SUM(CASE WHEN ag.debit_account = a.acct_id THEN ag.amount_paid ELSE 0 END) AS total_debit,
                SUM(CASE WHEN ag.credit_account = a.acct_id THEN ag.amount_paid ELSE 0 END) AS total_credit
            FROM accounts a
            LEFT JOIN account_general_transaction_new ag
                ON ag.debit_account = a.acct_id
                OR ag.credit_account = a.acct_id
            AND ag.approval_status='Approved'
            GROUP BY a.acct_id, a.acct_code, a.acct_alias, a.acct_class_type
            ORDER BY a.acct_code
        ";

        $st = $this->db->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
