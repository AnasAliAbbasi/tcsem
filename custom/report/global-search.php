<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Search - osTicket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <style>
        body {
            padding: 40px;
            background: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            color: #57d9ed;
        }

        h3 {
            text-align: center;
            margin-bottom: 40px;
            color: #57d9ed;
            font-weight: 600;
            font-size: 28px;
        }

        #searchKeyword {
            border: 2px solid #57d9ed;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            color: #57d9ed;
            background-color: #f6fcfd;
        }

        #searchKeyword::placeholder {
            color: #a3e6f0;
        }

        #searchKeyword:focus {
            box-shadow: 0 0 0 4px rgba(87, 217, 237, 0.3);
            border-color: #57d9ed;
            outline: none;
            background-color: #ffffff;
        }

        .dataTable thead th {
            background-color: #ffffff !important;
            color: #57d9ed !important;
            font-weight: bold;
            font-size: 16px;
            border-bottom: 2px solid #57d9ed;
        }

        .dataTable tbody td {
            color: #57d9ed;
        }

        .dataTable tbody tr:hover {
            background-color: #f1fcfe;
        }

        .btn-info {
            background-color: #57d9ed;
            border: none;
            color: white;
        }

        .btn-info:hover {
            background-color: #39c8dc;
        }

        .container {
            max-width: 900px;
            background: #f9fefe;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(87, 217, 237, 0.2);
            padding: 30px;
        }

        .dataTables_filter {
            display: none !important;
        }

        .dataTables_length,
        .dataTables_info {
            color: #57d9ed;
        }

.dataTables_paginate {
    margin-top: 20px;
    text-align: center;
}

.dataTables_paginate .paginate_button {
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px;
    padding: 6px 12px;
    margin: 2px;
    background-color: transparent;
    border: 1px solid #57d9ed;
    color: #57d9ed !important;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #e3faff;
    color: #57d9ed !important;
    border-color: #57d9ed;
    cursor: pointer;
}

.dataTables_paginate .paginate_button.current {
    background-color: #57d9ed !important;
    color: #fff !important;
    border-color: #57d9ed;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(87, 217, 237, 0.3);
}

.dataTables_info {
    font-size: 14px;
    color: #57d9ed;
    margin-top: 15px;
    text-align: center;
}

#searchTable {
    table-layout: fixed;
    width: 100%;
}

#searchTable td, #searchTable th {
    word-wrap: break-word;
}

    </style>
</head>
<body>

<div class="container">
    <h3>🔍 Global Search</h3>
    <input type="text" id="searchKeyword" class="form-control mb-4" placeholder="Search for FAQs, Tickets, Tasks..." />

    <table id="searchTable" class="table table-hover">
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    const table = $('#searchTable').DataTable({
    ajax: {
        url: 'globalSearch.php',
        data: function (d) {
            d.keyword = $('#searchKeyword').val();
        },
        dataSrc: 'data'
    },
    ordering: false,
    columns: [
        { data: 'type' },
        { data: 'title' },
        {
            data: 'link',
            render: function (data) {
                return `<a href="${data}" class="btn btn-sm btn-info" target="_blank">View</a>`;
            }
        }
    ],
    columnDefs: [
        { width: '10%', targets: 0 },  
        { width: '80%', targets: 1 },  
        { width: '10%', targets: 2 }   
    ],
    pageLength: 10,
    dom: 'lrtip',
    language: {
        emptyTable: "No results found. Try a different keyword."
    }
});


    $('#searchKeyword').on('keyup', function () {
        table.ajax.reload();
    });
});
</script>
</body>
</html>
