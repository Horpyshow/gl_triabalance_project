<?php
require_once 'BaseModel.php';

class LedgerModel extends BaseModel {

    public function getGeneralLedger($offset, $perPage) {
        // Main GL Query
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
        WHERE ag.approval_status = 'Approved'
        ORDER BY ag.date_of_payment DESC, ag.id DESC
        LIMIT $offset, $perPage
        ";

        $st = $this->db->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countGeneralLedger() {
        $sql = "SELECT COUNT(*) FROM account_general_transaction_new WHERE approval_status='Approved'";
        return $this->db->query($sql)->fetchColumn();
    }

    public function getLedgerForAccount($acct_id) {
        $sql = "
            SELECT 
                ag.id,
                ag.date_of_payment,
                ag.transaction_desc,
                ag.receipt_no,
                CASE WHEN ag.debit_account = ? THEN ag.amount_paid ELSE 0 END AS debit,
                CASE WHEN ag.credit_account = ? THEN ag.amount_paid ELSE 0 END AS credit
            FROM account_general_transaction_new ag
            WHERE ag.approval_status='Approved'
            AND (ag.debit_account = ? OR ag.credit_account = ?)
            ORDER BY ag.date_of_payment, ag.id
        ";

        $st = $this->db->prepare($sql);
        $st->execute(array($acct_id, $acct_id, $acct_id, $acct_id));
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
