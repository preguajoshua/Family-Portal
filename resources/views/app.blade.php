<!DOCTYPE html>
<html lang="en">

    <head>
    	<meta charset="utf-8">
    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1">

        <title>Family Portal</title>

        <!-- Styles -->
        <link rel="stylesheet" href="/css/vendor.css">
        <link rel="stylesheet" href="{% mix('/css/site.css') %}">
        <link rel="stylesheet" href="{% mix('/css/app.css') %}">

    	<!-- Fonts -->
        <link href="/css/font-awesome.css" rel="stylesheet">
    	<link href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css" rel="stylesheet">
    	<link href='//fonts.googleapis.com/css?family=Roboto:400,300,regular,medium,italic,mediumitalic,bold' rel='stylesheet' type='text/css'>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    	<!--[if lt IE 9]>
    		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    	<![endif]-->

        <!-- Braintree Client SDK -->
        <script src="/js/libs/braintree/client.min.js"></script>
        <script src="/js/libs/braintree/hosted-fields.min.js"></script>
        <!-- JavaScript framework -->
    	<script src="/js/libs/require.js"></script>
    </head>

    <body>
        <div id="preloader"><div class="status"></div></div>
        <div id="wrapper" class="wrapper"></div>

        <script type="text/stache" id="wrapper-content">
        	<div class="logo">
        	    <a href="/" class="simple-text"></a>
        	</div>
        	<sidebar class="sidebar"></sidebar>
            <div class="main-panel">
                <navbar id="navbar"></navbar>
                <div id="navbar-shadow"></div>
                <div id="content"></div>

                <footer class="footer">
                    <div class="container-fluid">
                    	<p class="copyright pull-right">Axxess &copy; <span><?php echo date('Y'); ?></span></p>
                    </div>
                </footer>
            </div>
        </script>

        <script type="text/javascript">
        	@if (config('app.env') == 'local')
            require( [ "/js/main.js" ]);
        	@else
        	require( ["{% mix('/js/dist/app.js') %}" ], function() {
        		require( [ "main" ] );
        	});
        	@endif
        </script>
    </body>
</html>
