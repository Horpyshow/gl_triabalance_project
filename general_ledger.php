<?php
include('includes/db.php');
// Pagination
$perPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max(1, $page);
$offset = ($page - 1) * $perPage;

// Sorting
$allowedSort = ['acct_code','date_of_payment','debit','credit'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort)
        ? $_GET['sort']
        : 'date_of_payment';

$order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc')
            ? 'ASC'
            : 'DESC';

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
ORDER BY {$sort} {$order}
LIMIT :offset, :perPage
";

$stmt = $db->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total rows for pagination
$countSql = "
SELECT COUNT(*) AS total
FROM account_general_transaction_new
WHERE approval_status='Approved'
";
$totalRows = $db->query($countSql)->fetchColumn();
$totalPages = ceil($totalRows / $perPage);
?>
<!DOCTYPE html>
<html>
<head>
    <title>General Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto p-6">

    <!-- HEADER -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-700">📘 General Ledger</h1>
        <p class="text-gray-500 mt-1">All approved debit and credit entries from the accounting system.</p>
    </div>

    <!-- SEARCH -->
    <div class="flex items-center justify-between mb-4">
        <input id="glSearch" 
               type="text"
               placeholder="Search account, description, amount, receipt..." 
               class="w-1/3 p-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-200">

        <a href="trial_balance.php" 
           class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700">
           View Trial Balance
        </a>
    </div>

    <!-- EXPORT BUTTONS -->
    <div class="flex gap-3 mb-4">
        <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow">
            Export Excel
        </button>

        <a href="gl_pdf.php?<?= http_build_query($_GET) ?>"
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">
           Export PDF
        </a>
    </div>
    <!-- TABLE -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm" id="gltTable">

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Account Code</th>
                    <th class="px-4 py-3 text-left">Account Name</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-center">Dr</th>
                    <th class="px-4 py-3 text-center">Cr</th>
                    <th class="px-4 py-3 text-center">Ref</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody id="glTable" class="divide-y">

            <?php foreach ($rows as $r): ?>
                <tr class="hover:bg-blue-50 transition">

                    <td class="px-4 py-2 text-gray-700"><?= htmlspecialchars($r['date_of_payment']) ?></td>

                    <td class="px-4 py-2 font-semibold"><?= htmlspecialchars($r['acct_code']) ?></td>

                    <td class="px-4 py-2 text-gray-700"><?= htmlspecialchars($r['acct_alias'] ?: $r['acct_desc']) ?></td>

                    <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($r['transaction_desc']) ?></td>

                    <!-- DR -->
                    <td class="px-4 py-2 text-center">
                        <?php if ($r['debit'] > 0): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full">
                                <?= number_format($r['debit'], 2) ?>
                            </span>
                        <?php else: ?> — <?php endif; ?>
                    </td>

                    <!-- CR -->
                    <td class="px-4 py-2 text-center">
                        <?php if ($r['credit'] > 0): ?>
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full">
                                <?= number_format($r['credit'], 2) ?>
                            </span>
                        <?php else: ?> — <?php endif; ?>
                    </td>

                    <td class="px-4 py-2 text-center text-gray-600">
                        <?= htmlspecialchars($r['receipt_no']) ?>
                    </td>

                    <!-- LINK TO TRANSACTION -->
                    <td class="px-4 py-2 text-center">
                        <a href="view_transaction.php?id=<?= $r['txn_id'] ?>"
                           class="text-blue-600 hover:text-blue-800 font-medium">
                           View
                        </a>
                    </td>

                </tr>
            <?php endforeach; ?>

            </tbody>

        </table>
    </div>

    <!-- PAGINATION -->
    <div class="flex justify-between items-center mt-6">

        <!-- Page info -->
        <p class="text-gray-600">
            Page <?= $page ?> of <?= $totalPages ?>  
            (<?= number_format($totalRows) ?> records)
        </p>

        <!-- Buttons -->
        <div class="flex space-x-2">

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&perPage=<?= $perPage ?>"
                   class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Prev</a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>&perPage=<?= $perPage ?>"
                   class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Next</a>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- FRONT-END SEARCH -->
<script>
document.getElementById("glSearch").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#glTable tr");

    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter)
            ? ""
            : "none";
    });
});
</script>

<script>
// Excel Export
function exportExcel() {
    var table = document.getElementById("gltTable");
    var wb = XLSX.utils.table_to_book(table);
    XLSX.writeFile(wb, "general_ledger.xlsx");
}
</script>

</body>
</html>

