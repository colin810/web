CREATE TRIGGER `cms_log_insert_trigger` AFTER INSERT ON `cms_value` FOR EACH ROW
BEGIN
	DECLARE
		tmp_key_code VARCHAR (50);

DECLARE
	tmp_system VARCHAR (50);

SELECT
	key_code,
	system INTO tmp_key_code,
	tmp_system
FROM
	cms_key
WHERE
	key_id = NEW.key_id;

INSERT INTO cms_log (
	key_id,
	key_code,
	value_id,
	system,
	lang,
	content,
	content_clean,
	remark,
	version,
	modify_clerk,
	modify_time,
	opt_flag
)
VALUES
	(
		NEW.key_id,
		tmp_key_code,
		NEW.value_id,
		tmp_system,
		NEW.lang,
		NEW.content,
		NEW.content_clean,
		NEW.remark,
		NEW.version,
		NEW.modify_clerk,
		unix_timestamp(now()),
		1
	);


END;

;
DELIMITER ;


CREATE TRIGGER `cms_log_update_trigger` AFTER UPDATE ON `cms_value` FOR EACH ROW
BEGIN
	DECLARE
		tmp_key_code VARCHAR (50);

DECLARE
	tmp_system VARCHAR (50);

SELECT
	key_code,
	system INTO tmp_key_code,
	tmp_system
FROM
	cms_key
WHERE
	key_id = NEW.key_id;

INSERT INTO cms_log (
	key_id,
	key_code,
	value_id,
	system,
	lang,
	content,
	content_clean,
	remark,
	version,
	modify_clerk,
	modify_time,
	opt_flag
)
VALUES
	(
		NEW.key_id,
		tmp_key_code,
		NEW.value_id,
		tmp_system,
		NEW.lang,
		NEW.content,
		NEW.content_clean,
		NEW.remark,
		NEW.version,
		NEW.modify_clerk,
		unix_timestamp(now()),
		2
	);


END;

;
DELIMITER ;


CREATE TRIGGER `cms_log_delete_trigger` BEFORE DELETE ON `cms_value` FOR EACH ROW
BEGIN
	DECLARE
		tmp_key_code VARCHAR (50);

DECLARE
	tmp_system VARCHAR (50);

SELECT
	key_code,
	system INTO tmp_key_code,
	tmp_system
FROM
	cms_key
WHERE
	key_id = OLD.key_id;

INSERT INTO cms_log (
	key_id,
	key_code,
	value_id,
	system,
	lang,
	content,
	content_clean,
	remark,
	version,
	modify_clerk,
	modify_time,
	opt_flag
)
VALUES
	(
		OLD.key_id,
		tmp_key_code,
		OLD.value_id,
		tmp_system,
		OLD.lang,
		OLD.content,
		OLD.content_clean,
		OLD.remark,
		OLD.version,
		OLD.modify_clerk,
		unix_timestamp(now()),
		3
	);


END;

;
DELIMITER ;
