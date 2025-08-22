-- 在线自学考试系统数据库表结构
-- 创建数据库（如果不存在）
CREATE DATABASE IF NOT EXISTS exame_system DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE exame_system;

-- 会员表：存储用户信息
CREATE TABLE IF NOT EXISTS member (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '会员ID',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT '邮箱地址',
    register_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
    login_time DATETIME DEFAULT NULL COMMENT '最后登录时间',
    INDEX idx_email (email),
    INDEX idx_login_time (login_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员信息表';

-- 试卷表：存储试卷信息
CREATE TABLE IF NOT EXISTS exam (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '试卷ID',
    title VARCHAR(255) NOT NULL COMMENT '试卷标题',
    nums INT NOT NULL DEFAULT 0 COMMENT '题目数量',
    score INT NOT NULL DEFAULT 0 COMMENT '试卷总分',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    INDEX idx_create_time (create_time),
    INDEX idx_update_time (update_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试卷信息表';

-- 题目表：存储题目信息
CREATE TABLE IF NOT EXISTS question (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '题目ID',
    exam_id INT NOT NULL COMMENT '所属试卷ID',
    title TEXT NOT NULL COMMENT '题目内容',
    options JSON NOT NULL COMMENT '选项内容，以ABCD为选项序号的JSON格式',
    type TINYINT(1) NOT NULL COMMENT '题目类型：1单选，2多选',
    answer VARCHAR(10) NOT NULL COMMENT '正确答案，存储ABCD格式',
    analysis VARCHAR(500) NOT NULL DEFAULT '' COMMENT '题目解析',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    FOREIGN KEY (exam_id) REFERENCES exam(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_exam_id (exam_id),
    INDEX idx_type (type),
    INDEX idx_create_time (create_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题目信息表';

-- 错题本：存储用户做错的题目
CREATE TABLE IF NOT EXISTS mistake (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '错题ID',
    title TEXT NOT NULL COMMENT '题目内容',
    exam_id INT NOT NULL COMMENT '所属试卷ID',
    options JSON NOT NULL COMMENT '选项内容，以ABCD为选项序号的JSON格式',
    type TINYINT(1) NOT NULL COMMENT '题目类型：1单选，2多选',
    answer VARCHAR(10) NOT NULL COMMENT '正确答案',
    errors VARCHAR(10) NOT NULL COMMENT '用户当时回答的内容',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    member_id INT NOT NULL COMMENT '用户ID',
    question_id INT NOT NULL COMMENT '原题目ID',
    FOREIGN KEY (exam_id) REFERENCES exam(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (member_id) REFERENCES member(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (question_id) REFERENCES question(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_member_id (member_id),
    INDEX idx_exam_id (exam_id),
    INDEX idx_create_time (create_time),
    INDEX idx_question_id (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='错题本信息表';

-- 插入测试数据
-- 插入测试会员
INSERT INTO member (email, register_time, login_time) VALUES 
('test@example.com', NOW(), NOW()),
('demo@example.com', NOW(), NOW());

-- 插入测试试卷
INSERT INTO exam (title, nums, score) VALUES 
('PHP基础知识测试', 5, 100),
('JavaScript基础测试', 4, 80),
('MySQL数据库测试', 6, 120);

-- 插入测试题目
-- PHP试卷题目
INSERT INTO question (exam_id, title, options, type,answer, analysis) VALUES 
(1, 'PHP中用于输出字符串的函数是？', '{"A":"echo()","B":"print()","C":"printf()","D":"sprintf()"}', 1, 'A','测试'),
(1, 'PHP变量的正确命名方式是？', '{"A":"$变量名","B":"@变量名","C":"#变量名","D":"%变量名"}', 1, 'A','测试'),
(1, '以下哪个是PHP的数组函数？', '{"A":"array_push()","B":"push_array()","C":"array_add()","D":"add_array()"}', 1, 'A','测试'),
(1, 'PHP中连接数据库的扩展是？', '{"A":"mysqli","B":"mysql","C":"pdo","D":"以上都是"}', 2, 'AD','测试'),
(1, 'PHP的全称是什么？', '{"A":"Personal Home Page","B":"PHP: Hypertext Preprocessor","C":"Private Home Page","D":"Professional Home Page"}', 1, 'B','测试');

-- JavaScript试卷题目
INSERT INTO question (exam_id, title, options, type, answer, analysis) VALUES 
(2, 'JavaScript中声明变量的关键字是？', '{"A":"var","B":"let","C":"const","D":"以上都是"}', 2, 'ABCD','测试'),
(2, '以下哪个不是JavaScript的数据类型？', '{"A":"string","B":"number","C":"boolean","D":"char"}', 1, 'D','测试'),
(2, 'JavaScript中用于获取DOM元素的方法是？', '{"A":"getElementById()","B":"getElementsByClassName()","C":"querySelector()","D":"以上都是"}', 2, 'ABCD','测试'),
(2, 'JavaScript中数组的添加元素方法是？', '{"A":"push()","B":"add()","C":"append()","D":"insert()"}', 1, 'A','测试');

-- MySQL试卷题目
INSERT INTO question (exam_id, title, options, type, answer, analysis) VALUES 
(3, 'MySQL中用于创建数据库的语句是？', '{"A":"CREATE DATABASE","B":"MAKE DATABASE","C":"NEW DATABASE","D":"BUILD DATABASE"}', 1, 'A','测试'),
(3, 'MySQL中主键的关键字是？', '{"A":"PRIMARY KEY","B":"MAIN KEY","C":"KEY PRIMARY","D":"UNIQUE KEY"}', 1, 'A','测试'),
(3, '以下哪些是MySQL的数据类型？', '{"A":"INT","B":"VARCHAR","C":"TEXT","D":"STRING"}', 2, 'ABC','测试'),
(3, 'MySQL中用于排序的关键字是？', '{"A":"ORDER BY","B":"SORT BY","C":"GROUP BY","D":"ARRANGE BY"}', 1, 'A','测试'),
(3, 'MySQL中用于连接查询的关键字是？', '{"A":"JOIN","B":"CONNECT","C":"LINK","D":"COMBINE"}', 1, 'A','测试'),
(3, 'MySQL中用于删除表的语句是？', '{"A":"DROP TABLE","B":"DELETE TABLE","C":"REMOVE TABLE","D":"CLEAR TABLE"}', 1, 'A','测试');

-- 插入测试错题
INSERT INTO mistake (title, exam_id, options, type, answer, errors, member_id, question_id) VALUES 
('PHP中用于输出字符串的函数是？', 1, '{"A":"echo()","B":"print()","C":"printf()","D":"sprintf()"}', 1, 'A', 'B', 1, 1),
('JavaScript中声明变量的关键字是？', 2, '{"A":"var","B":"let","C":"const","D":"以上都是"}', 2, 'ABCD', 'A', 1, 6),
('MySQL中用于创建数据库的语句是？', 3, '{"A":"CREATE DATABASE","B":"MAKE DATABASE","C":"NEW DATABASE","D":"BUILD DATABASE"}', 1, 'A', 'B', 2, 11);