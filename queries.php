<?php
//Quick hack to get PostgreSQL running and remove redundancy

if(config_get_global( 'db_type' ) == "mysqli")
{
    function set_global_dbmode()
    {
        return "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    }

    function get_vote_overview()
    {
        $plugin_table = plugin_table("support_data");
        $bug_table = db_get_table('mantis_bug_table');
        $dbquery = "SELECT
            max(sd.bugid) as bugid,
            count(sd.rating) as no_of_ratings,
            sum(sd.rating) as sum_of_ratings,
            avg(sd.rating) as avg_rating,
            max(sd.rating) as highest_rating,
            min(sd.rating) as lowest_rating,
            IFNULL(bm2_count,0) AS bm2_count,
            IFNULL(bm2_sum,0) AS bm2_sum,
            IFNULL(bm1_count,0) AS bm1_count,
            IFNULL(bm1_sum,0) AS bm1_sum,
            IFNULL(b2_count,0) AS b2_count,
            IFNULL(b2_sum,0) AS b2_sum,
            IFNULL(b1_count,0) AS b1_count,
            IFNULL(b1_sum,0) AS b1_sum
        FROM {$plugin_table} sd
        INNER JOIN {$bug_table} b ON sd.bugid = b.id
        LEFT OUTER JOIN (SELECT bugid, count(rating) as bm2_count, sum(rating) as bm2_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = -2) bm2 ON sd.bugid = bm2.bugid
        LEFT OUTER JOIN (SELECT bugid, count(rating) as bm1_count, sum(rating) as bm1_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = -1) bm1 ON sd.bugid = bm1.bugid
        LEFT OUTER JOIN (SELECT bugid, count(rating) as b2_count, sum(rating) as b2_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = 2) b2 ON sd.bugid = b2.bugid
        LEFT OUTER JOIN (SELECT bugid, count(rating) as b1_count, sum(rating) as b1_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = 1) b1 ON sd.bugid = b1.bugid
        {$where_clause}
        GROUP BY sd.bugid
        ORDER BY sum(sd.rating) DESC ";    
    }

    function insert_vote()
    {
        $dbtable = plugin_table("support_data","GaugeSupport");
        return "INSERT INTO {$dbtable} (bugid, userid, rating) VALUES (".db_param().",".db_param().",".db_param().") ON DUPLICATE KEY UPDATE rating = ".db_param();
    }

    function get_bug_ratings($bugid)
    {
        $dbtable = plugin_table("support_data");
        $dbquery = "SELECT userid, rating FROM {$dbtable} WHERE bugid=$bugid";    
    }
}
elseif(config_get_global( 'db_type' ) == "pgsql")
{
    //no set_global_dbmode for PostgreSQL
    function get_vote_overview()
    {
        $plugin_table = plugin_table("support_data");
        $bug_table = db_get_table('mantis_bug_table');
        return "SELECT
            max(sd.bugid) as bugid,
            count(sd.rating) as no_of_ratings,
            sum(sd.rating) as sum_of_ratings,
            avg(sd.rating) as avg_rating,
            max(sd.rating) as highest_rating,
            min(sd.rating) as lowest_rating,
            max(coalesce(bm2_count,0)) AS bm2_count,
            max(coalesce(bm2_sum,0)) AS bm2_sum,
            max(coalesce(bm1_count,0)) AS bm1_count,
            max(coalesce(bm1_sum,0)) AS bm1_sum,
            max(coalesce(b2_count,0)) AS b2_count,
            max(coalesce(b2_sum,0)) AS b2_sum,
            max(coalesce(b1_count,0)) AS b1_count,
            max(coalesce(b1_sum,0)) AS b1_sum
        FROM {$plugin_table} sd
        INNER JOIN {$bug_table} b ON sd.bugid = b.id
        LEFT OUTER JOIN (SELECT bugid, count(rating) as bm2_count, sum(rating) as bm2_sum FROM {$plugin_table} WHERE rating = -2 GROUP BY bugid) bm2 ON sd.bugid = bm2.bugid
        LEFT OUTER JOIN (SELECT bugid, count(rating) as bm1_count, sum(rating) as bm1_sum FROM {$plugin_table} WHERE rating = -1 GROUP BY bugid) bm1 ON sd.bugid = bm1.bugid
        LEFT OUTER JOIN (SELECT bugid, count(rating) as b2_count, sum(rating) as b2_sum FROM {$plugin_table} WHERE rating = 2 GROUP BY bugid) b2 ON sd.bugid = b2.bugid
        LEFT OUTER JOIN (SELECT bugid, count(rating) as b1_count, sum(rating) as b1_sum FROM {$plugin_table} WHERE rating = 1 GROUP BY bugid) b1 ON sd.bugid = b1.bugid
        {$where_clause}
        GROUP BY sd.bugid
        ORDER BY sum(sd.rating) DESC ";            
    }

    function insert_vote()
    {
        $dbtable = plugin_table("support_data","GaugeSupport");
        //key name guessed :(
        return "INSERT INTO {$dbtable} (bugid, userid, rating) 
                VALUES (".db_param().",".db_param().",".db_param().") 
                ON CONFLICT ON CONSTRAINT ". $dbtable. "_pkey DO UPDATE SET rating = ".db_param();
    }

    function get_bug_ratings($bugid)
    {
        $dbtable = plugin_table("support_data");
        $dbquery = "SELECT userid, rating FROM {$dbtable} WHERE bugid=$bugid";    
    }
}
else
    throw ErrorException("Database not supported"); //no idea how mantisbt handles errors
