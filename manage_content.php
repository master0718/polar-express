<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['admin']['logged_in']) || $_SESSION['admin']['logged_in'] !== true) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="text_editor/bootstrap-wysihtml5/lib/css/bootstrap.min.css">
    </link>
    <link rel="stylesheet" type="text/css" href="text_editor/bootstrap-wysihtml5/lib/css/prettify.css">
    </link>
    <link rel="stylesheet" type="text/css" href="text_editor/bootstrap-wysihtml5/src/bootstrap-wysihtml5.css">
    </link>
    <title>Manage Site Content</title>

    <style type="text/css" media="screen">
        .btn.jumbo {
            font-size: 20px;
            font-weight: normal;
            padding: 14px 24px;
            margin-right: 10px;
            -webkit-border-radius: 6px;
            -moz-border-radius: 6px;
            border-radius: 6px;
        }
    </style>

    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-30181385-1']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>
</head>

<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center">Manage Site Content</h1>
        <div class="hero-unit" style="margin-top:40px">
            <textarea class="textarea" placeholder="Enter text ..." style="width: 810px; height: 200px"></textarea>
            <button class="btn btn-primary">submit</button>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <script src="text_editor/bootstrap-wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
    <script src="text_editor/bootstrap-wysihtml5/lib/js/jquery-1.7.2.min.js"></script>
    <script src="text_editor/bootstrap-wysihtml5/lib/js/prettify.js"></script>
    <script src="text_editor/bootstrap-wysihtml5/lib/js/bootstrap.min.js"></script>
    <script src="text_editor/bootstrap-wysihtml5/src/bootstrap-wysihtml5.js"></script>

    <script>
        $('.textarea').wysihtml5();
    </script>

    <script type="text/javascript" charset="utf-8">
        $(prettyPrint);
    </script>

</body>

</html>