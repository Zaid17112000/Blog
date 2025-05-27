<?php
    function formatFollowers($followerCount) {
        $formatted_followers = '';

        if ($followerCount === 0) {
            $formatted_followers = "0 Followers";
        }
        elseif ($followerCount < 1000) {
            $formatted_followers = $followerCount . ($followerCount === 1 ? " Follower" : " Followers");
        }
        elseif ($followerCount < 10000) {
            $formatted_followers = number_format($followerCount / 1000, 1) . 'K';
        }
        elseif ($followerCount >= 10000) {
            $formatted_followers = floor($followerCount / 1000) . 'K';
        }
        return htmlspecialchars($formatted_followers);
    }