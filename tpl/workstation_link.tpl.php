
	[view.liste;strconv=no]

	<div class="tabsAction">
			<input class="butAction" type="submit" value="[view.langs.transnoentities(Save)]" name="bt_save" />
			<a href="#" class="butAction btnaddworkstation">[view.langs.transnoentities(AddWorkstation)]</a>
	</div>
	<div id="dialog-workstation" title="[view.langs.transnoentities(AddWorkstation)]"  style="display:none;">
		<table>
			<tr>
				<td>[view.langs.transnoentities(Workstations)] : </td>
				<td>
					[view.select_workstation;strconv=no]
				</td>
			</tr>
		</table>
	</div>
	<script type="text/javascript">

		$(".btnaddworkstation" ).click(function() {

				$( "#dialog-workstation" ).dialog({
					show: {
						effect: "blind",
						duration: 200
					},
					modal:true,
					buttons: {
						"[view.langs.transnoentities(Cancel)]": function() {
							$( this ).dialog( "close" );
						},
						"[view.langs.transnoentities(Add)]": function(){

							var fk_workstation = $('#fk_workstation').val();

							document.location.href="?fk_product=[view.fk_product]&action=add&fk_workstation="+fk_workstation+"[urlToken]";
						}
					}
				});
			});


	</script>
