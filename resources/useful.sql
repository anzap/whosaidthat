-- Query to get random entries from mytable
select * from mytable offset random() * (select count(*) from mytable) limit 1 ;

-- Find non answered status
select * from statuses 
where id NOT IN (
	select status_id from answers where user_id = this_user_id
	) 
offset random() * (select count(*) from statuses) limit 1 ;