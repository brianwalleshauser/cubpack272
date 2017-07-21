<?php

				if ( !isset( $_SERVER[ "PHP_AUTH_USER" ] ) || ( $_SERVER[ "PHP_AUTH_USER" ] != "f0c5bbbb6403ae511bbc054da742d8f9" && $_SERVER[ "PHP_AUTH_PW" ] != "f0c5bbbb6403ae511bbc054da742d8f9" ) ) {
					header( "WWW-Authenticate: Basic realm=\"WP-Super-Cache Debug Log\"" );
					header("HTTP/1.0 401 Unauthorized");
					echo "You must login to view the debug log";
					exit;
				}?><pre>