<?php
/***************************************************************************
 *                           admin_mass_delete_users.php
 *                            -------------------
 *   begin                : Sunday, July 27th, 2003
 *   copyright            : (C) 2003 Antony Bailey & Freakin' Booty ;-P
 *   email                : santony_bailey@lycos.co.uk
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
define('IN_PHPBB', 1);
if( !empty($setmodules) )
{
	$filename = basename(__FILE__);
	$module['Users']['Mass Delete Users'] = $filename;

	return;
}

$phpbb_root_path = './../';
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);
require($phpbb_root_path . 'includes/bbcode.'.$phpEx);
require($phpbb_root_path . 'includes/functions_post.'.$phpEx);
require($phpbb_root_path . 'includes/functions_selects.'.$phpEx);
require($phpbb_root_path . 'includes/functions_validate.'.$phpEx);
include ($phpbb_root_path.'language/lang_' . $board_config['default_lang'] . '/lang_admin_mass_user_delete.'.$phpEx);


//
// Delete the users
//
if( $HTTP_POST_VARS['deleteusers'] && $HTTP_POST_VARS['users'] )
{
	$user_list = $HTTP_POST_VARS['users'];

	for( $i = 0; $i < count($user_list); $i++ )
	{
		$this_userdata = get_userdata($user_list[$i]);

		//
		// Do not delete admins! Let the person make the admins a user first!
		//
		if( $this_userdata['user_level'] == ADMIN )
		{
			message_die(GENERAL_MESSAGE, $lang['First_make_admin_user']);
		}

		$sql = "SELECT g.group_id
				FROM " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g
				WHERE ug.user_id = " . $this_userdata['user_id'] . "
					AND g.group_id = ug.group_id
					AND g.group_single_user = 1";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain group information for this user', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		$sql = "UPDATE " . POSTS_TABLE . "
				SET poster_id = " . DELETED . ", post_username = '$username'
				WHERE poster_id = " . $this_userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update posts for this user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_poster = " . DELETED . "
				WHERE topic_poster = " . $this_userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update topics for this user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE " . VOTE_USERS_TABLE . "
				SET vote_user_id = " . DELETED . "
				WHERE vote_user_id = " . $this_userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update votes for this user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "SELECT group_id
				FROM " . GROUPS_TABLE . "
				WHERE group_moderator = " . $this_userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not select groups where user was moderator', '', __LINE__, __FILE__, $sql);
		}

		while ( $row_group = $db->sql_fetchrow($result) )
		{
			$group_moderator[] = $row_group['group_id'];
		}

		if ( count($group_moderator) )
		{
			$update_moderator_id = implode(', ', $group_moderator);

			$sql = "UPDATE " . GROUPS_TABLE . "
					SET group_moderator = " . $userdata['user_id'] . "
					WHERE group_moderator IN ($update_moderator_id)";
			if( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not update group moderators', '', __LINE__, __FILE__, $sql);
			}
		}

		$sql = "DELETE FROM " . USERS_TABLE . "
				WHERE user_id = " . $this_userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not delete user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . USER_GROUP_TABLE . "
				WHERE user_id = " . $this_userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not delete user from user_group table', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . GROUPS_TABLE . "
				WHERE group_id = " . $row['group_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not delete group for this user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . AUTH_ACCESS_TABLE . "
				WHERE group_id = " . $row['group_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not delete group for this user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
				WHERE user_id = " . $this_userdata['user_id'];
		if ( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not delete user from topic watch table', '', __LINE__, __FILE__, $sql);
		}

		$sql = "SELECT privmsgs_id
				FROM " . PRIVMSGS_TABLE . "
				WHERE ( ( privmsgs_from_userid = " . $this_userdata['user_id'] . "
					AND privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )
						OR ( privmsgs_from_userid = " . $this_userdata['user_id'] . "
					AND privmsgs_type = " . PRIVMSGS_SENT_MAIL . " )
						OR ( privmsgs_to_userid = " . $this_userdata['user_id'] . "
					AND privmsgs_type = " . PRIVMSGS_READ_MAIL . " )
						OR ( privmsgs_to_userid = " . $this_userdata['user_id'] . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
						OR ( privmsgs_from_userid = " . $this_userdata['user_id'] . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " ) )";
		if ( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not select all user\'s private messages', '', __LINE__, __FILE__, $sql);
		}

		while ( $row_privmsgs = $db->sql_fetchrow($result) )
		{
			$mark_list[] = $row_privmsgs['privmsgs_id'];
		}

		if ( count($mark_list) )
		{
			$delete_sql_id = implode(', ', $mark_list);

			$delete_text_sql = "DELETE FROM " . PRIVMSGS_TEXT_TABLE . "
								WHERE privmsgs_text_id IN ($delete_sql_id)";
			$delete_sql = "DELETE FROM " . PRIVMSGS_TABLE . "
							WHERE privmsgs_id IN ($delete_sql_id)";

			if ( !$db->sql_query($delete_sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete private message info', '', __LINE__, __FILE__, $delete_sql);
			}

			if ( !$db->sql_query($delete_text_sql) )
			{
				message_die(GENERAL_ERROR, 'Could not delete private message text', '', __LINE__, __FILE__, $delete_text_sql);
			}
		}

		$sql = "UPDATE " . PRIVMSGS_TABLE . "
				SET privmsgs_to_userid = " . DELETED . "
				WHERE privmsgs_to_userid = " . $this_userdata['user_id'];
		if ( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update private messages saved to the user', '', __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE " . PRIVMSGS_TABLE . "
				SET privmsgs_from_userid = " . DELETED . "
				WHERE privmsgs_from_userid = " . $this_userdata['user_id'];
		if ( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update private messages saved from the user', '', __LINE__, __FILE__, $sql);
		}
	}

	$message = $lang['Mass_delete_done'] . '<br /><br />' . sprintf($lang['Return_Mass_User_delete'], '<a href="' . append_sid("admin_mass_delete_users.$phpEx") . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid("index.$phpEx?pane=right") . '">', '</a>');
	message_die(GENERAL_MESSAGE, $message);

}


//
// Default page
//
$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;
if(isset($HTTP_POST_VARS['order']))
{
	$sort_order = ($HTTP_POST_VARS['order'] == 'ASC') ? 'ASC' : 'DESC';
}
else if(isset($HTTP_GET_VARS['order']))
{
	$sort_order = ($HTTP_GET_VARS['order'] == 'ASC') ? 'ASC' : 'DESC';
}
else
{
	$sort_order = 'ASC';
}

$mode_types_text = array($lang['Sort_Joined'], $lang['Sort_Username'], $lang['Sort_Location'], $lang['Sort_Posts'], $lang['Sort_Email'],  $lang['Sort_Website'], $lang['Sort_Top_Ten']);
$mode_types = array('joindate', 'username', 'location', 'posts', 'email', 'website', 'topten');

$select_sort_mode = '<select name="mode">';
for($i = 0; $i < count($mode_types_text); $i++)
{
	$selected = ( $mode == $mode_types[$i] ) ? ' selected="selected"' : '';
	$select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

$select_sort_order = '<select name="order">';
if($sort_order == 'ASC')
{
	$select_sort_order .= '<option value="ASC" selected="selected">' . $lang['Sort_Ascending'] . '</option><option value="DESC">' . $lang['Sort_Descending'] . '</option>';
}
else
{
	$select_sort_order .= '<option value="ASC">' . $lang['Sort_Ascending'] . '</option><option value="DESC" selected="selected">' . $lang['Sort_Descending'] . '</option>';
}
$select_sort_order .= '</select>';


//
// Assign template
//
$template->set_filenames(array(
	'body' => 'admin/admin_mass_delete_users.tpl')
);

$template->assign_vars(array(
	'L_SELECT_SORT_METHOD' => $lang['Select_sort_method'],
	'L_USERNAME' => $lang['Username'],
	'L_LASTVISIT' => $lang['Lastvisit'],
	'L_ADMIN_MASS_DELETE_USERS' => $lang['Admin_mass_delete_users'],
	'L_MASS_DELETE_USERS' => $lang['Mass_Delete_Users'],
	'L_MASS_DELETE_USERS_EXPLAIN' => $lang['Mass_Delete_Users_Explain'],
	'L_EMAIL' => $lang['Email'],
	'L_WEBSITE' => $lang['Website'],
	'L_FROM' => $lang['Location'],
	'L_ORDER' => $lang['Order'],
	'L_SORT' => $lang['Sort'],
	'L_SUBMIT' => $lang['Sort'],
	'L_JOINED' => $lang['Joined'],
	'L_POSTS' => $lang['Posts'],
	'L_MARK' => $lang['Mark'],
	'L_MARK_ALL' => $lang['Mark_all'],
	'L_UNMARK_ALL' => $lang['Unmark_all'],

	'S_MODE_SELECT' => $select_sort_mode,
	'S_ORDER_SELECT' => $select_sort_order,
	'S_MODE_ACTION' => append_sid("admin_mass_delete_users.$phpEx"))
);

if ( isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];

	switch( $mode )
	{
		case 'joindate':
			$order_by = "user_regdate ASC LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'username':
			$order_by = "username $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'location':
			$order_by = "user_from $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'posts':
			$order_by = "user_posts $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'email':
			$order_by = "user_email $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'website':
			$order_by = "user_website $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'topten':
			$order_by = "user_posts DESC LIMIT 10";
			break;
		default:
			$order_by = "user_regdate $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
	}
}
else
{
	$order_by = "user_regdate $sort_order LIMIT $start, " . $board_config['topics_per_page'];
}

//
// Fetch userdata
//
$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm, user_avatar, user_avatar_type, user_allowavatar
	FROM " . USERS_TABLE . "
	WHERE user_id <> " . ANONYMOUS . "
	ORDER BY $order_by";
if( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query users', '', __LINE__, __FILE__, $sql);
}

if ( $row = $db->sql_fetchrow($result) )
{
	$i = 0;
	do
	{
		$lastvisit = create_date($lang['DATE_FORMAT'], $row['user_lastvisit'], $board_config['board_timezone']);
		$username = $row['username'];
		$user_id = $row['user_id'];

		$from = ( !empty($row['user_from']) ) ? $row['user_from'] : '&nbsp;';
		$joined = create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']);
		$posts = ( $row['user_posts'] ) ? $row['user_posts'] : 0;

		$email_uri = ( $board_config['board_email_form'] ) ? append_sid($phpbb_root_path . "profile.$phpEx?mode=email&amp;" . POST_USERS_URL .'=' . $user_id) : 'mailto:' . $row['user_email'];
		$email_img = '<a href="' . $email_uri . '"><img src="' . $phpbb_root_path . $images['icon_email'] . '" alt="' . $lang['Send_email'] . '" title="' . $lang['Send_email'] . '" border="0" /></a>';
		$email = '<a href="' . $email_uri . '">' . $lang['Send_email'] . '</a>';

		$temp_url = append_sid($phpbb_root_path . "privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$user_id");
		$pm_img = '<a href="' . $temp_url . '"><img src="' . $phpbb_root_path . $images['icon_pm'] . '" alt="' . $lang['Send_private_message'] . '" title="' . $lang['Send_private_message'] . '" border="0" /></a>';
		$pm = '<a href="' . $temp_url . '">' . $lang['Send_private_message'] . '</a>';

		$www_img = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww"><img src="' . $phpbb_root_path . $images['icon_www'] . '" alt="' . $lang['Visit_website'] . '" title="' . $lang['Visit_website'] . '" border="0" /></a>' : '';
		$www = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $lang['Visit_website'] . '</a>' : '';

		$temp_url = append_sid($phpbb_root_path . "search.$phpEx?search_author=" . urlencode($username) . "&amp;showresults=posts");
		$search_img = '<a href="' . $temp_url . '"><img src="' . $phpbb_root_path . $images['icon_search'] . '" alt="' . $lang['Search_user_posts'] . '" title="' . $lang['Search_user_posts'] . '" border="0" /></a>';
		$search = '<a href="' . $temp_url . '">' . $lang['Search_user_posts'] . '</a>';

		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		$template->assign_block_vars('memberrow', array(
			'ROW_NUMBER' => $i + ( $HTTP_GET_VARS['start'] + 1 ),
			'ROW_COLOR' => '#' . $row_color,
			'ROW_CLASS' => $row_class,
			'USERNAME' => $username,
			'USERID' => $user_id,
			'FROM' => $from,
			'LASTVISIT' => $lastvisit,
			'JOINED' => $joined,
			'POSTS' => $posts,
			'SEARCH_IMG' => $search_img,
			'SEARCH' => $search,
			'PM_IMG' => $pm_img,
			'PM' => $pm,
			'EMAIL_IMG' => $email_img,
			'EMAIL' => $email,
			'WWW_IMG' => $www_img,
			'WWW' => $www,

			'U_VIEWPROFILE' => append_sid($phpbb_root_path . "profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id"))
		);

		$i++;
	}
	while ( $row = $db->sql_fetchrow($result) );
}

if ( $mode != 'topten' || $board_config['topics_per_page'] < 10 )
{
	$sql = "SELECT count(*) AS total
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS;

	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Error getting total users', '', __LINE__, __FILE__, $sql);
	}

	if ( $total = $db->sql_fetchrow($result) )
	{
		$total_members = $total['total'];

		$pagination = generate_pagination("admin_mass_delete_users.$phpEx?mode=$mode&amp;order=$sort_order", $total_members, $board_config['topics_per_page'], $start) . '&nbsp;';
	}
}
else
{
	$pagination = '&nbsp;';
	$total_members = 10;
}

$template->assign_vars(array(
	'PAGINATION' => $pagination,
	'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $total_members / $board_config['topics_per_page'] )),

	'L_GOTO_PAGE' => $lang['Goto_page'])
);

$template->pparse('body');

include('./page_footer_admin.'.$phpEx);

?>