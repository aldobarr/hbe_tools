@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<p>Here you can download the Britannian Nation Parser browser plugin so that you can have your nation sign in automatically. Just install this plugin and completely forget about signing in, no one will bother you about it again. If you encounter any issue(s) with the plugin, please contact a member of IA.</p><br>
						</div>
					</div>
					<br>
					<div class="row text-center">
						<div class="col-md-6"><button type="button" class="btn btn-primary" id="chrome">Chrome Plugin</button></div>
						<div class="col-md-6"><button type="button" class="btn btn-primary" id="firefox" iconURL="{!! $icon !!}" hash="{!! $hash !!}">Firefox Plugin</button></div>
					</div>
				</div>
			</div>
		</div>
		<script type="application/javascript">
			$("#chrome").click(function(){
				if(navigator.userAgent.match(/chrome/i)){
					var win = window.open("https://chrome.google.com/webstore/detail/britannian-nation-parser/oflpkfcclpiffdinhhemaomgfnbilfoo", "_blank");
					win.focus();
				}else
					alert("This version of the plugin only supports Chrome browsers.");
			});
			$("#firefox").click(function(){
				if(navigator.userAgent.match(/firefox/i)){
					var params = {
						"HBE": {
							URL: "{!! url('/firefox') !!}",
							IconURL: $(this).attr("iconURL"),
							Hash: $(this).attr("hash"),
							toString: function(){ return this.URL; }
						}
					};
					InstallTrigger.install(params);
				}else
					alert("This version of the plugin only supports Firefox browsers.");
			});
		</script>
@stop