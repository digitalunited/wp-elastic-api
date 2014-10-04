<!DOCTYPE html>
<html>
<head>
	<title>WP Elastic API</title>
	<link href='https://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css' />
	<link href='<?php echo $url; ?>/css/reset.css' media='screen' rel='stylesheet' type='text/css' />
	<link href='<?php echo $url; ?>/css/screen.css' media='screen' rel='stylesheet' type='text/css' />
	<link href='<?php echo $url; ?>/css/reset.css' media='print' rel='stylesheet' type='text/css' />
	<link href='<?php echo $url; ?>/css/screen.css' media='print' rel='stylesheet' type='text/css' />
	<script type="text/javascript" src="<?php echo $url; ?>/lib/shred.bundle.js"></script>
	<script src='<?php echo $url; ?>/lib/jquery-1.8.0.min.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/jquery.slideto.min.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/jquery.wiggle.min.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/jquery.ba-bbq.min.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/handlebars-1.0.0.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/underscore-min.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/backbone-min.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/swagger.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/swagger-ui.js' type='text/javascript'></script>
	<script src='<?php echo $url; ?>/lib/highlight.7.3.pack.js' type='text/javascript'></script>

	<!-- enabling this will enable oauth2 implicit scope support -->
	<script src='<?php echo $url; ?>/lib/swagger-oauth.js' type='text/javascript'></script>

	<script type="text/javascript">
		$(function () {
			window.swaggerUi = new SwaggerUi({
				url                   : "<?php echo $json_url; ?>",
				dom_id                : "swagger-ui-container",
				supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
				onComplete            : function (swaggerApi, swaggerUi) {
					log("Loaded SwaggerUI");

					if (typeof initOAuth == "function") {
						/*
						 initOAuth({
						 clientId: "your-client-id",
						 realm: "your-realms",
						 appName: "your-app-name"
						 });
						 */
					}
					$('pre code').each(function (i, e) {
						hljs.highlightBlock(e)
					});
				},
				onFailure             : function (data) {
					log("Unable to Load SwaggerUI");
				},
				docExpansion          : "none"
			});

			$('#input_apiKey').change(function () {
				var key = $('#input_apiKey')[0].value;
				log("key: " + key);
				if (key && key.trim() != "") {
					log("added key " + key);
					window.authorizations.add("key", new ApiKeyAuthorization("api_key", key, "query"));
				}
			})
			window.swaggerUi.load();
		});
	</script>
</head>

<body class="swagger-section">

<div style="text-align: center; margin-top: 10px;">
	<a href="<?php echo '/api/api-doc'; ?>">
		<img src="<?php echo $url; ?>/images/elasticsearch.png" alt="WP Elastic API" title="WP Elastic API" />
	</a>
</div>

<div id="message-bar" class="swagger-ui-wrap">&nbsp;</div>
<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
</body>
</html>
