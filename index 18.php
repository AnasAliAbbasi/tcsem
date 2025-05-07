<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ABC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
     .card {
      background: #ffffff;
      border-radius: 15px;
      border: 2px solid skyblue;
      box-shadow: 0 8px 16px rgba(0, 173, 239, 0.2);
      padding: 20px;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      height: 100%;
      position: relative;
      overflow: hidden;
    }

    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 16px 30px rgba(0, 173, 239, 0.3);
      border-color: #007bbf;
    }

    .card .icon {
      font-size: 30px;
      color: skyblue;
      margin-bottom: 10px;
    }

    .card-title {
      font-size: 20px;
      font-weight: 700;
      color: #007bbf;
      text-align: center;
      margin-bottom: 10px;
    }

    .card-title a {
      color: inherit;
      text-decoration: none;
      transition: color 0.3s;
    }

    .card-title a:hover {
      color: #00a1ff;
    }

    .card-message {
      font-size: 15px;
      color: #333;
      text-align: left;
      line-height: 1.6;
    }

    .truncate {
      /* white-space: nowrap; */
      overflow: hidden;
      text-overflow: ellipsis;
      display: block;
      max-width: 100%;
    }

    .container {
      padding-top: 50px;
      padding-bottom: 50px;
    }

    .col-md-3 {
      margin-bottom: 30px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">

    <div class="col-md-3">
      <div class="card">
        <div class="text-center icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-title">
          <a class="truncate" href="kb2.php?cid=1">Human Resources</a>
        </div>
        <div class="card-message">
          <p><strong>Total FAQs:</strong> 2</p>
          <p><strong>Visibility:</strong> Public</p>
          <p>HR policies</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card">
        <div class="text-center icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-title">
          <a class="truncate" href="kb2.php?cid=2">ISO 9001-2015 Documents</a>
        </div>
        <div class="card-message">
          <p><strong>Total FAQs:</strong> 35</p>
          <p><strong>Visibility:</strong> Public</p>
          <p>Department Documents and Manual of ISO 9001-2015 Standards</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card">
        <div class="text-center icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-title">
          <a class="truncate" href="kb2.php?cid=3">OS Ticket How to</a>
        </div>
        <div class="card-message">
          <p><strong>Total FAQs:</strong> 2</p>
          <p><strong>Visibility:</strong> Public</p>
          <p>Please post all help and training documents related to OST under this category.<p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card">
        <div class="text-center icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-title">
          <a class="truncate" href="kb2.php?cid=4">SEM Customers Related Documents</a>
        </div>
        <div class="card-message">
          <p><strong>Total FAQs:</strong> 4</p>
          <p><strong>Visibility:</strong> Private</p>
          <p>SEM Customers Related Documents</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card">
        <div class="text-center icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-title">
          <a class="truncate" href="kb2.php?cid=5">SEM Departments</a>
        </div>
        <div class="card-message">
          <p><strong>Total FAQs:</strong> 7</p>
          <p><strong>Visibility:</strong> Public</p>
          <p>SEM Departments Related Documents</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card">
        <div class="text-center icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="card-title">
          <a class="truncate" href="kb2.php?cid=6">OS Ticket How to</a>
        </div>
        <div class="card-message">
          <p><strong>Total FAQs:</strong> 2</p>
          <p><strong>Visibility:</strong> Public</p>
          <p>Please post all help and training documents related to OST under this category.<p>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
