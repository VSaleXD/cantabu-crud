
<?php // layout/header.php ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cantabu</title>
  <style>
    :root{--pink:#e91e63;--pink-dark:#c2185b;--pink-light:#f8bbd0;--text:#333}
    *{box-sizing:border-box}
    body{font-family:Arial,sans-serif;margin:0;color:var(--text)}
    header{background:linear-gradient(90deg,var(--pink),var(--pink-dark));color:#fff;padding:16px 20px;box-shadow:0 2px 8px rgba(0,0,0,.15)}
    header h1{margin:0;font-size:20px}
    .container{max-width:1000px;margin:18px auto;padding:0 14px}
    .card{background:#fff;border:1px solid #eee;border-radius:10px;padding:16px;box-shadow:0 2px 10px rgba(233,30,99,.15)}
    a{text-decoration:none}
    .btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid var(--pink);color:var(--pink);background:#fff;transition:.2s}
    .btn:hover{background:var(--pink-light)}
    .btn-primary{background:var(--pink);color:#fff;border-color:var(--pink)}
    .btn-primary:hover{background:var(--pink-dark)}
    .btn-danger{background:#b00020;color:#fff;border-color:#b00020}
    table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #eee;padding:10px}
    th{background:var(--pink-light)}
    .muted{color:#777}
  </style>
</head>
<body>
<header><h1>Cantabu</h1></header>
<div class="container">
