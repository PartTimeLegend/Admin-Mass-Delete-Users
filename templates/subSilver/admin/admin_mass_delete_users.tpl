<h1>{L_ADMIN_MASS_DELETE_USERS}</h1>
<p>{L_MASS_DELETE_USERS_EXPLAIN}</p>

<script language="JavaScript" type="text/javascript">
<!--
	function check_switch(val)
	{
		for( i = 0; i < document.post.elements.length; i++ )
		{
			document.post.elements[i].checked = val;
		}
	}
//-->
</script>

<form method="post" action="{S_MODE_ACTION}" name="post">
  <table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="right" nowrap="nowrap"><span class="genmed">{L_SELECT_SORT_METHOD}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp; 
		<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
		</span></td>
	</tr>
  </table>
  <table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
	<tr> 
	  <th height="25" class="thCornerL" nowrap="nowrap">#</th>
	  <th class="thTop" nowrap="nowrap">{L_USERNAME}</th>
	  <th class="thTop" nowrap="nowrap">&nbsp;</th>
	  <th class="thTop" nowrap="nowrap">{L_EMAIL}</th>
	  <th class="thTop" nowrap="nowrap">{L_WEBSITE}</th>
	  <th class="thTop" nowrap="nowrap">{L_FROM}</th>
	  <th class="thTop" nowrap="nowrap">{L_JOINED}</th>
	  <th class="thTop" nowrap="nowrap">{L_LASTVISIT}</th>
	  <th class="thTop" nowrap="nowrap">{L_POSTS}</th>
	  <th class="thCornerR" nowrap="nowrap">{L_MARK}</th>
	</tr>
	<!-- BEGIN memberrow -->
	<tr> 
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen">&nbsp;{memberrow.ROW_NUMBER}&nbsp;</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"><a href="{memberrow.U_VIEWPROFILE}" class="gen">{memberrow.USERNAME}</a></span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center">&nbsp;{memberrow.PM_IMG}&nbsp;</td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle">&nbsp;{memberrow.EMAIL_IMG}&nbsp;</td>
	  <td class="{memberrow.ROW_CLASS}" align="center">&nbsp;{memberrow.WWW_IMG}&nbsp;</td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.FROM}</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gensmall">{memberrow.JOINED}</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gensmall">{memberrow.LASTVISIT}</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.POSTS}</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen"><input type="checkbox" name="users[]" value="{memberrow.USERID}"></span></td>
	</tr>
	<!-- END memberrow -->
	<tr> 
	  <td class="catBottom" colspan="10" height="28"><input type="submit" name="deleteusers" value="{L_MASS_DELETE_USERS}" class="liteoption" /></td>
	</tr>
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="right" valign="top"><span class="nav"><a href="javascript:check_switch(true);" class="nav">{L_MARK_ALL}</a> :: <a href="javascript:check_switch();" class="nav">{L_UNMARK_ALL}</a></span></td>
	</tr>
  </table>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr> 
	<td><span class="nav">{PAGE_NUMBER}</span></td>
	<td align="right"><span class="gensmall">{S_TIMEZONE}</span><br /><span class="nav">{PAGINATION}</span></td>
  </tr>
</table></form>
