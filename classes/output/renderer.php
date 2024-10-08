<?php
namespace block_course_completion_stats\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/badges/renderer.php');
use context_course;
use moodle_url;
use html_writer;
use pix_icon;

class renderer extends \core_badges_renderer {
    public function print_badges_list($badges, $userid, $profile = false, $external = false) {
        global $USER, $CFG;
        
        foreach ($badges as $badge) {
            if (!$external) {
                echo "this funct is working";
                $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
                $bname = $badge->name;
                $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f3', false);
            } else {
                $bname = '';
                $imageurl = '';
                if (!empty($badge->name)) {
                    $bname = s($badge->name);
                }
                if (!empty($badge->image)) {
                    if (is_object($badge->image)) {
                        if (!empty($badge->image->caption)) {
                            $badge->imagecaption = $badge->image->caption;
                        }
                        $imageurl = $badge->image->id;
                    } else {
                        $imageurl = $badge->image;
                    }
                }
                if (isset($badge->assertion->badge->name)) {
                    $bname = s($badge->assertion->badge->name);
                }
                if (isset($badge->imageUrl)) {
                    $imageurl = $badge->imageUrl;
                }
            }

            // $name = html_writer::tag('span', $bname, array('class' => 'badge-name'));

            $imagecaption = $badge->imagecaption ?? '';
            $image = html_writer::empty_tag('img', ['src' => $imageurl, 'class' => 'badge-image', 'alt' => $imagecaption]);
            if (!empty($badge->dateexpire) && $badge->dateexpire < time()) {
                $image .= $this->output->pix_icon('i/expired',
                        get_string('expireddate', 'badges', userdate($badge->dateexpire)),
                        'moodle',
                        array('class' => 'expireimage'));
                $name .= '(' . get_string('expired', 'badges') . ')';
            }

            $download = $status = $push = '';
            if (($userid == $USER->id) && !$profile) {
                $params = array(
                    'download' => $badge->id,
                    'hash' => $badge->uniquehash,
                    'sesskey' => sesskey()
                );
                $url = new moodle_url(
                    'mybadges.php',
                    $params
                );
                $notexpiredbadge = (empty($badge->dateexpire) || $badge->dateexpire > time());
                $userbackpack = badges_get_user_backpack();
                if (!empty($CFG->badges_allowexternalbackpack) && $notexpiredbadge && $userbackpack) {
                    $assertion = new moodle_url('/badges/assertion.php', array('b' => $badge->uniquehash));
                    $icon = new pix_icon('t/backpack', get_string('addtobackpack', 'badges'));
                    if (badges_open_badges_backpack_api($userbackpack->id) == OPEN_BADGES_V2) {
                        $addurl = new moodle_url('/badges/backpack-add.php', array('hash' => $badge->uniquehash));
                        $push = $this->output->action_icon($addurl, $icon);
                    } else if (badges_open_badges_backpack_api($userbackpack->id) == OPEN_BADGES_V2P1) {
                        $addurl = new moodle_url('/badges/backpack-export.php', array('hash' => $badge->uniquehash));
                        $push = $this->output->action_icon($addurl, $icon);
                    }
                }

                $download = $this->output->action_icon($url, new pix_icon('t/download', get_string('download')));
                if ($badge->visible) {
                    $url = new moodle_url('mybadges.php', array('hide' => $badge->issuedid, 'sesskey' => sesskey()));
                    $status = $this->output->action_icon($url, new pix_icon('t/hide', get_string('makeprivate', 'badges')));
                } else {
                    $url = new moodle_url('mybadges.php', array('show' => $badge->issuedid, 'sesskey' => sesskey()));
                    $status = $this->output->action_icon($url, new pix_icon('t/show', get_string('makepublic', 'badges')));
                }
            }

            if (!$profile) {
                $url = new moodle_url('badge.php', array('hash' => $badge->uniquehash));
            } else {
                if (!$external) {
                    $url = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
                } else {
                    $hash = hash('md5', $badge->hostedUrl);
                    $url = new moodle_url('/badges/external.php', array('hash' => $hash, 'user' => $userid));
                }
            }
            // $actions = html_writer::tag('div', $push . $download . $status, array('class' => 'badge-actions'));
            // $items[] = html_writer::link($url, $image . $actions . $name, array('title' => $bname));
            $items[] = $image . $actions . $name;

        }

        return html_writer::alist($items, array('class' => 'badges'));
    }
}