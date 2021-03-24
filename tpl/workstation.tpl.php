
<div>
	<table width="100%" class="border">
		<tr><td width="20%">[view.langs.transnoentities(Label)]</td><td>[ws.name; strconv=no]</td></tr>
		<tr><td width="20%">[view.langs.transnoentities(CodeMaybe)]</td><td>[ws.code; strconv=no]</td></tr>
		<tr><td width="20%">[view.langs.transnoentities(Type)]</td><td>[onshow;block=tr;when [ws.simple]!=1][ws.type; strconv=no]</td></tr>
		[onshow;block=begin;when [view.isSTT]==0]
		<tr><td width="20%">[onshow;block=tr;when [view.isMachine]==0][view.langs.transnoentities(UsersGroup)]</td><td>[ws.fk_usergroup; strconv=no]</td></tr>
		<tr style="display: none;"><td>[onshow;block=tr;when [ws.simple]==1]<input type="hidden" name="type" id="type" value="HUMAN"></td></tr>
		<tr><td width="20%">[view.langs.transnoentities(NbHourCapacity)]</td><td>[ws.nb_hour_capacity; strconv=no]</td></tr>
		<tr><td>[view.langs.transnoentities(NbHourBeforeProduction)]</td><td>[ws.nb_hour_before; strconv=no]h</td></tr>
		<tr><td>[view.langs.transnoentities(NbHourAfterProduction)]</td><td>[ws.nb_hour_after; strconv=no]h</td></tr>


	    <tr><td width="20%">[view.langs.transnoentities(AvailaibleRessources)]</td><td>[ws.nb_ressource; strconv=no]</td></tr>
        <tr><td>[onshow;block=tr;when [view.isMachine]==0][view.langs.transnoentities(THM)]</td><td>[ws.thm; strconv=no]</td></tr>
        <tr><td>[onshow;block=tr;when [view.isMachine]==0][view.langs.transnoentities(THMHeuresSup)]</td><td>[ws.thm_overtime; strconv=no]</td></tr>
        <tr><td>[onshow;block=tr;when [view.isMachine]==0][view.langs.transnoentities(THMNuit)]</td><td>[ws.thm_night; strconv=no]</td></tr>
        <tr><td width="20%">[view.langs.transnoentities(THMMachine)]</td><td>[onshow;block=tr;when [ws.simple]!=1][ws.thm_machine; strconv=no]</td></tr>
        <tr><td>[view.langs.transnoentities(IsParallele)]</td><td>[ws.is_parallele; strconv=no]</td></tr>

        [onshow;block=end]
        <tr><td width="20%">[view.langs.transnoentities(ColumnColor)]</td><td>[onshow;block=tr;when [ws.simple]!=1][ws.background; strconv=no]</td></tr>
	</table>
</div>


[onshow;block=begin;when [view.mode]!='edit']
[onshow;block=begin;when [view.can_delete]==1]
    <div class="tabsAction">
        <a href="?id=[ws.id]&action=edit" class="butAction">[view.langs.transnoentities(Modify)]</a>
		<a href="?id=[ws.id]&action=delete[urlToken]" class="butActionDelete" onclick="if (!confirm('Confirmez-vous la suppression ?')) return false;" >[view.langs.transnoentities(Delete)]</a>
    </div>
[onshow;block=end]
[onshow;block=end]

[onshow;block=begin;when [view.isSTT]==0]
[view.scheduleTitle;strconv=no;]
<div style="margin-top:15px;">
    <table width="100%" class="border">
        <tr class="liste_titre">
            <th align="left" width="10%">[view.langs.transnoentities(Date)]</th>
            <th>[view.langs.transnoentities(OrDayInWeek)]</th>
            <th>[view.langs.transnoentities(DayPeriod)]</th>
            <th>[view.langs.transnoentities(NbRessourceAvailable)]</th>
            <th>[view.langs.transnoentities(NbHourCapacity)]</th>
            <th>&nbsp;</th>
        </tr>

        <tr style="background-color:#fff;">
            <td>[TWorkstationSchedule.date_off;block=tr;strconv=no]</td>
            <td>[TWorkstationSchedule.week_day;strconv=no]</td>
            <td>[TWorkstationSchedule.day_moment;strconv=no]</td>
            <td>[TWorkstationSchedule.nb_ressource;strconv=no]</td>
            <td>[TWorkstationSchedule.nb_hour_capacity;strconv=no]</td>
            <td align="center">[TWorkstationSchedule.action;strconv=no]</td>
        </tr>

        <tr>
            <td colspan="4" align="center">[TWorkstationSchedule;block=tr;nodata][view.langs.transnoentities(NoPlannedTime)]</td>
        </tr>
    </table>
</div>

 [onshow;block=end]

[onshow;block=begin;when [view.mode]=='edit']
    <div class="tabsAction" style="text-align:center;">
        <input type="submit" value="[view.langs.transnoentities(Save)]" name="save" class="button">
        &nbsp; &nbsp; <input type="button" value="[view.langs.transnoentities(Cancel)]" name="cancel" class="button" onclick="document.location.href='[view.cancelUrl]'">

    </div>
[onshow;block=end]


[onshow;block=begin;when [view.conf_defined_task]==1]
	[onshow;block=begin;when [view.editTask]=='1']
		<div style="margin-top:15px;">
				<input type="hidden" name="id_task" value="[formTask.id_task;noerr]" />

				<table width="100%" class="border">
					<tr><th align="left" colspan="2">[formTask.id_task;noerr;if [val]==0;then '[view.langs.transnoentities(AddTask)]';else '[view.langs.transnoentities(EditTask)]']</th></tr>
					<tr><td>[view.langs.transnoentities(Label)]</td><td><input size="45" type="text" name="TWSTask[libelle]" value="[formTask.libelle;noerr;strconv=no]" /></td></tr>
					<tr><td>[view.langs.transnoentities(Description)]</td><td><textarea cols="45" rows="3" name="TWSTask[description]">[formTask.description;noerr;strconv=no]</textarea></td></tr>
				</table>

				<div class="tabsAction" style="text-align:center;">
					<input class="button" type="submit" value=[view.langs.transnoentities(Save)] />
					<a style="font-weight:normal;text-decoration:none" href="?action=view&id=[ws.id]" class="button">[view.langs.transnoentities(Cancel)]</a>
				</div>
			<!-- </form> -->
		</div>
	[onshow;block=end]
[onshow;block=end]

[onshow;block=begin;when [view.conf_defined_task]==1]
	<div style="margin-top:15px;">
		<table width="100%" class="border">
			<tr height="40px;">
				<td colspan="4">&nbsp;&nbsp;<strong>[view.langs.transnoentities(ModeOperatoire)]</strong></td>
			</tr>
			<tr style="background-color:#dedede;">
				<th align="left" width="10%">&nbsp;&nbsp;[view.langs.transnoentities(Task)]</th>
				<th align="left" width="30%">&nbsp;&nbsp;[view.langs.transnoentities(Description)]</th>
				<th align="center" width="5%">&nbsp;&nbsp;[view.langs.transnoentities(Action)]</th>
			</tr>

			<tr style="background-color:#fff;">
				<td>&nbsp;&nbsp;[wst.libelle;strconv=no;block=tr]</td>
				<td>[wst.description;strconv=no;block=tr]</td>
				<td align="center">[wst.action;strconv=no;block=tr]</td>
			</tr>

			<tr>
				<td colspan="4" align="center">[wst;block=tr;nodata][view.langs.transnoentities(NoAssociatedTask)]</td>
			</tr>
		</table>
	</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='edit']
	<div class="tabsAction">
		[onshow;block=begin;when [view.conf_defined_task]==1]
			<a href="?id=[ws.id]&action=editTask" class="butAction">[view.langs.transnoentities(AddTask)]</a>
		[onshow;block=end]
	</div>
[onshow;block=end]


<div style="clear:both"></div>

