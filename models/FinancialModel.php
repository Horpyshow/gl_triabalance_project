<?php
require_once 'BaseModel.php';

class FinancialModel extends BaseModel {

    public function getAllTransactionsForIFRS() {
        $sql = "
            SELECT 
                a.acct_id,
                a.acct_code,
                a.acct_alias,
                a.acct_desc,
                ag.id AS txn_id,
                ag.date_of_payment,
                ag.transaction_desc,
                ag.receipt_no,
                CASE WHEN ag.debit_account = a.acct_id THEN ag.amount_paid ELSE 0 END AS debit,
                CASE WHEN ag.credit_account = a.acct_id THEN ag.amount_paid ELSE 0 END AS credit
            FROM accounts a
            LEFT JOIN account_general_transaction_new ag
                ON ag.debit_account = a.acct_id
                OR ag.credit_account = a.acct_id
            WHERE ag.approval_status='Approved'
            ORDER BY a.acct_code, ag.date_of_payment
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
