<div class="p-6 bg-white shadow rounded-xl">
    <h1 class="text-2xl font-bold mb-4">Financial Period Closing</h1>

    <form action="?action=run-closing" method="POST" class="space-y-4">
        <label class="block">
            <span>Select Accounting Period to Close</span>
            <input type="month" name="period" class="border p-2 rounded w-full" required>
        </label>

        <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
            Run Closing Entries
        </button>
    </form>
</div>
