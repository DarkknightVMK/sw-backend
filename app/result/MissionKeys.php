<?
namespace App\result;

class MissionKeys
{


  public const missionInfo = array 
  (
      'missionchain_desc',
      'active',
      'avatar_fname',
      'missionchain_xpleveltype_id',
      'missionchain_min_xplevel',
      'missionchain_entry_tokens',
      'avatar_lname',
      'missionchain_activation_group',
      'avatar_name_instance',
      'missionchain_follow_on_missionchain_id',
      'missionchain_panel_style',
      'missionchain_follow_on_automatic',
      'missionchain_expires',
      'missionchain_completed_desc',
      'missionchain_title',
      'missionchain_id',
      'missionchain_total_plays',
      'missionchain_rating',
      'missionchain_votes',
      'average_time',
      'cooloff',
      'missionchain_cooldown_time_override',
      'already_active',
      'mission_owner',
      'too_many_active_missions',
      'avatar_head_postfix',
      'avatar_thumb_postfix',
      'avatar_snapshot_postfix',
  );      // end of missionInfo array

  const activeMissions = array 
  (
      'missionchain_desc',
      'expired',
      'avatarmission_pickup_space_id',
      'avatar_fname',
      'space_desc',
      'avatar_head_postfix',
      'missionchain_xpleveltype_id',
      'space_icon_source',
      'space_model_id',
      'avatar_lname',
      'missionchain_activation_group',
      'mission_missionchain_id',
      'missionchain_avatar_id',
      'mission_desc',
      'missionchain_first_mission_id',
      'missionchain_user_id',
      'mission_completed_desc',
      'avatar_name_instance',
      'space_thumbnail_source',
      'space_access_control',
      'missionchain_follow_on_missionchain_id',
      'mission_script',
      'avatar_thumb_postfix',
      'space_owner_id',
      'avatar_snapshot_postfix',
      'missionchain_panel_style',
      'missionchain_follow_on_automatic',
      'avatarmission_mission_id',
      'mission_time_left',
      'mission_title',
      'missionchain_expires',
      'avatarmission_activated',
      'avatarmission_is_test',
      'avatarmission_pickup_space_name',
      'active',
      'avatarmission_pickup_location_vector',
      'missionchain_completed_desc',
      'missionchain_title',
  );

  public const featuredMissions = array
  (
    // MissionChain
    'missionchain_desc',
    'missionchain_xpleveltype_id',
    'missionchain_min_xplevel',
    'missionchain_last_activation_space_id',
    'missionchain_avatar_id',
    'missionchain_activation_group',
    'missionchain_follow_on_missionchain_id',
    'missionchain_panel_style',
    'missionchain_follow_on_automatic',
    'missionchain_expires',
    'missionchain_total_plays',
    'missionchain_title',
    'missionchain_completed_desc',
    'missionchain_votes',
    'missionchain_good_mission',
    'missionchain_rating',
    'missionchain_id',
    'missionchain_bonus_model_id',
    'missionchain_entry_tokens',
    'missionchain_bonus_tokens',
    'missionchain_timestamp',
    'missionchain_first_play_timestamp',
    'missionchain_total_playtime',
    'missionchain_user_id',
    'missionchain_xp',

    // SPACES 
    'space_thumbnail_source',
    'space_model_id',
    'space_desc',
    'space_access_control',
    'space_owner_id',
    'space_icon_source',
    'spacerole_access',
    
    // AVATAR
    'avatar_fname',
    'avatar_lname',
    'avatar_head_postfix',
    'avatar_thumb_postfix',
    'avatar_snapshot_postfix',
    'avatar_name_instance',
    
    'average_time',

     //EXTRA delete?
    'bonus_rewards',
    'now_time',
    'average_reward_xp',
    'average_reward_tokens',
    

  );




}