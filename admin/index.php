<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Accounting System</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<!-- MAIN WRAPPER -->
<div class="flex h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-gray-700">
            Accounting
        </div>

        <nav class="flex-1 p-4 space-y-2">

            <a href="?action=gl"
               class="block px-4 py-2 rounded-lg 
               <?= ($_GET['action'] ?? 'gl')=='gl'?'bg-blue-600':'hover:bg-gray-700' ?>">
                General Ledger
            </a>

            <a href="?action=trial-balance"
               class="block px-4 py-2 rounded-lg 
               <?= ($_GET['action'] ?? '')=='trial-balance'?'bg-blue-600':'hover:bg-gray-700' ?>">
                Trial Balance
            </a>

            <a href="?action=ifrs"
               class="block px-4 py-2 rounded-lg 
               <?= ($_GET['action'] ?? '')=='ifrs'?'bg-blue-600':'hover:bg-gray-700' ?>">
                IFRS Financials
            </a>

            <a href="?action=closing"
               class="block px-4 py-2 rounded-lg 
               <?= ($_GET['action'] ?? '')=='closing'?'bg-blue-600':'hover:bg-gray-700' ?>">
                Closing & Opening
            </a>

        </nav>
    </aside>

    <!-- CONTENT AREA -->
    <main class="flex-1 p-6 overflow-y-auto">
        <?= $content ?>
    </main>

</div>

</body>
</html>
