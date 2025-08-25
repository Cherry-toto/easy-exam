/*
 Navicat Premium Data Transfer

 Source Server         : 本地数据库
 Source Server Type    : MySQL
 Source Server Version : 50728
 Source Host           : localhost:3306
 Source Schema         : exame_system

 Target Server Type    : MySQL
 Target Server Version : 50728
 File Encoding         : 65001

 Date: 25/08/2025 15:35:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码（MD5加密）',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1正常，0禁用',
  `salt` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '加密字符',
  `login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `login_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录IP',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  INDEX `idx_username`(`username`) USING BTREE,
  INDEX `idx_login_time`(`login_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员信息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 'admin', 'c7935cc8ee50b752345290d8cf136827', '', 1, 'abcdef', '2025-08-25 13:41:38', '2025-08-23 08:52:20', '127.0.0.1');

-- ----------------------------
-- Table structure for exam
-- ----------------------------
DROP TABLE IF EXISTS `exam`;
CREATE TABLE `exam`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '试卷ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '试卷标题',
  `nums` int(11) NOT NULL DEFAULT 0 COMMENT '题目数量',
  `score` int(11) NOT NULL DEFAULT 0 COMMENT '试卷总分',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE,
  INDEX `idx_update_time`(`update_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '试卷信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of exam
-- ----------------------------
INSERT INTO `exam` VALUES (1, 'PHP基础知识测试', 15, 75, '2025-08-21 10:10:54', '2025-08-25 13:49:15');
INSERT INTO `exam` VALUES (2, 'JavaScript基础测试', 4, 80, '2025-08-21 10:10:54', '2025-08-21 10:10:54');
INSERT INTO `exam` VALUES (3, 'MySQL数据库测试', 6, 120, '2025-08-21 10:10:54', '2025-08-21 10:10:54');

-- ----------------------------
-- Table structure for exam_log
-- ----------------------------
DROP TABLE IF EXISTS `exam_log`;
CREATE TABLE `exam_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '考试记录ID',
  `exam_id` int(11) NOT NULL COMMENT '试卷ID',
  `member_id` int(11) NOT NULL COMMENT '用户ID',
  `score` decimal(5, 2) NOT NULL COMMENT '考试分数',
  `use_time` int(11) NOT NULL DEFAULT 0 COMMENT '所用时间（秒）',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '考试时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_member_id`(`member_id`) USING BTREE,
  INDEX `idx_exam_id`(`exam_id`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE,
  CONSTRAINT `exam_log_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `exam_log_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '考试记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of exam_log
-- ----------------------------
INSERT INTO `exam_log` VALUES (1, 2, 3, 40.00, 45, '2025-08-25 14:39:36');
INSERT INTO `exam_log` VALUES (2, 1, 1, 75.00, 2943, '2025-08-25 15:23:04');
INSERT INTO `exam_log` VALUES (3, 2, 1, 95.00, 1986, '2025-08-25 15:23:04');

-- ----------------------------
-- Table structure for member
-- ----------------------------
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员ID',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱地址',
  `register_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE,
  INDEX `idx_email`(`email`) USING BTREE,
  INDEX `idx_login_time`(`login_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '会员信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of member
-- ----------------------------
INSERT INTO `member` VALUES (1, 'test@example.com', '2025-08-21 10:10:54', '2025-08-21 10:10:54');
INSERT INTO `member` VALUES (2, 'demo@example.com', '2025-08-21 10:10:54', '2025-08-21 10:10:54');
INSERT INTO `member` VALUES (3, '2581047041@qq.com', '2025-08-21 15:13:28', '2025-08-21 15:13:28');

-- ----------------------------
-- Table structure for mistake
-- ----------------------------
DROP TABLE IF EXISTS `mistake`;
CREATE TABLE `mistake`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '错题ID',
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '题目内容',
  `exam_id` int(11) NOT NULL COMMENT '所属试卷ID',
  `options` json NOT NULL COMMENT '选项内容，以ABCD为选项序号的JSON格式',
  `type` tinyint(1) NOT NULL COMMENT '题目类型：1单选，2多选',
  `answer` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '正确答案',
  `errors` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户当时回答的内容',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `member_id` int(11) NOT NULL COMMENT '用户ID',
  `question_id` int(11) NOT NULL COMMENT '原题目ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_member_id`(`member_id`) USING BTREE,
  INDEX `idx_exam_id`(`exam_id`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE,
  INDEX `idx_question_id`(`question_id`) USING BTREE,
  CONSTRAINT `mistake_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mistake_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mistake_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '错题本信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mistake
-- ----------------------------
INSERT INTO `mistake` VALUES (1, 'PHP中用于输出字符串的函数是？', 1, '{\"A\": \"echo()\", \"B\": \"print()\", \"C\": \"printf()\", \"D\": \"sprintf()\"}', 1, 'A', 'B', '2025-08-21 10:10:54', 1, 1);
INSERT INTO `mistake` VALUES (2, 'JavaScript中声明变量的关键字是？', 2, '{\"A\": \"var\", \"B\": \"let\", \"C\": \"const\", \"D\": \"以上都是\"}', 2, 'ABCD', 'A', '2025-08-21 10:10:54', 1, 6);
INSERT INTO `mistake` VALUES (3, 'MySQL中用于创建数据库的语句是？', 3, '{\"A\": \"CREATE DATABASE\", \"B\": \"MAKE DATABASE\", \"C\": \"NEW DATABASE\", \"D\": \"BUILD DATABASE\"}', 1, 'A', 'B', '2025-08-21 10:10:54', 2, 11);
INSERT INTO `mistake` VALUES (4, 'PHP变量的正确命名方式是？', 1, '{\"A\": \"$变量名\", \"B\": \"@变量名\", \"C\": \"#变量名\", \"D\": \"%变量名\"}', 1, 'A', 'B', '2025-08-21 15:29:55', 3, 2);
INSERT INTO `mistake` VALUES (5, '以下哪个是PHP的数组函数？', 1, '{\"A\": \"array_push()\", \"B\": \"push_array()\", \"C\": \"array_add()\", \"D\": \"add_array()\"}', 1, 'A', 'C', '2025-08-21 15:29:55', 3, 3);
INSERT INTO `mistake` VALUES (6, 'PHP中连接数据库的扩展是？', 1, '{\"A\": \"mysqli\", \"B\": \"mysql\", \"C\": \"pdo\", \"D\": \"以上都是\"}', 2, 'AD', '', '2025-08-21 15:29:55', 3, 4);
INSERT INTO `mistake` VALUES (7, 'JavaScript中声明变量的关键字是？', 2, '{\"A\": \"var\", \"B\": \"let\", \"C\": \"const\", \"D\": \"以上都是\"}', 2, 'ABCD', 'B', '2025-08-21 15:33:33', 3, 6);
INSERT INTO `mistake` VALUES (8, '以下哪个不是JavaScript的数据类型？', 2, '{\"A\": \"string\", \"B\": \"number\", \"C\": \"boolean\", \"D\": \"char\"}', 1, 'D', '', '2025-08-21 15:33:33', 3, 7);
INSERT INTO `mistake` VALUES (9, 'JavaScript中用于获取DOM元素的方法是？', 2, '{\"A\": \"getElementById()\", \"B\": \"getElementsByClassName()\", \"C\": \"querySelector()\", \"D\": \"以上都是\"}', 2, 'ABCD', 'A', '2025-08-21 15:33:33', 3, 8);
INSERT INTO `mistake` VALUES (10, 'JavaScript中数组的添加元素方法是？', 2, '{\"A\": \"push()\", \"B\": \"add()\", \"C\": \"append()\", \"D\": \"insert()\"}', 1, 'A', '', '2025-08-21 15:33:33', 3, 9);
INSERT INTO `mistake` VALUES (11, 'JavaScript中声明变量的关键字是？', 2, '{\"A\": \"var\", \"B\": \"let\", \"C\": \"const\", \"D\": \"以上都是\"}', 2, 'ABCD', 'AC', '2025-08-21 15:45:47', 3, 6);
INSERT INTO `mistake` VALUES (12, '以下哪个不是JavaScript的数据类型？', 2, '{\"A\": \"string\", \"B\": \"number\", \"C\": \"boolean\", \"D\": \"char\"}', 1, 'D', 'C', '2025-08-21 15:45:47', 3, 7);
INSERT INTO `mistake` VALUES (13, 'JavaScript中用于获取DOM元素的方法是？', 2, '{\"A\": \"getElementById()\", \"B\": \"getElementsByClassName()\", \"C\": \"querySelector()\", \"D\": \"以上都是\"}', 2, 'ABCD', 'B', '2025-08-21 15:45:47', 3, 8);
INSERT INTO `mistake` VALUES (14, 'JavaScript中数组的添加元素方法是？', 2, '{\"A\": \"push()\", \"B\": \"add()\", \"C\": \"append()\", \"D\": \"insert()\"}', 1, 'A', 'B', '2025-08-21 15:45:47', 3, 9);
INSERT INTO `mistake` VALUES (15, 'PHP变量的正确命名方式是？', 1, '{\"A\": \"$变量名\", \"B\": \"@变量名\", \"C\": \"#变量名\", \"D\": \"%变量名\"}', 1, 'A', 'B', '2025-08-21 16:21:35', 3, 2);
INSERT INTO `mistake` VALUES (16, 'PHP中连接数据库的扩展是？', 1, '{\"A\": \"mysqli\", \"B\": \"mysql\", \"C\": \"pdo\", \"D\": \"以上都是\"}', 2, 'AD', '', '2025-08-21 16:21:35', 3, 4);
INSERT INTO `mistake` VALUES (17, 'PHP的全称是什么？', 1, '{\"A\": \"Personal Home Page\", \"B\": \"PHP: Hypertext Preprocessor\", \"C\": \"Private Home Page\", \"D\": \"Professional Home Page\"}', 1, 'B', '', '2025-08-21 16:21:35', 3, 5);
INSERT INTO `mistake` VALUES (18, 'PHP中用于输出字符串的函数是？', 1, '{\"A\": \"echo()\", \"B\": \"print()\", \"C\": \"printf()\", \"D\": \"sprintf()\"}', 1, 'A', 'C', '2025-08-21 17:12:50', 3, 1);
INSERT INTO `mistake` VALUES (19, 'PHP变量的正确命名方式是？', 1, '{\"A\": \"$变量名\", \"B\": \"@变量名\", \"C\": \"#变量名\", \"D\": \"%变量名\"}', 1, 'A', 'D', '2025-08-21 17:12:50', 3, 2);
INSERT INTO `mistake` VALUES (20, '以下哪个是PHP的数组函数？', 1, '{\"A\": \"array_push()\", \"B\": \"push_array()\", \"C\": \"array_add()\", \"D\": \"add_array()\"}', 1, 'A', 'B', '2025-08-21 17:12:50', 3, 3);
INSERT INTO `mistake` VALUES (21, 'PHP中连接数据库的扩展是？', 1, '{\"A\": \"mysqli\", \"B\": \"mysql\", \"C\": \"pdo\", \"D\": \"以上都是\"}', 2, 'AD', 'C', '2025-08-21 17:12:50', 3, 4);
INSERT INTO `mistake` VALUES (22, 'PHP的全称是什么？', 1, '{\"A\": \"Personal Home Page\", \"B\": \"PHP: Hypertext Preprocessor\", \"C\": \"Private Home Page\", \"D\": \"Professional Home Page\"}', 1, 'B', 'C', '2025-08-21 17:12:50', 3, 5);
INSERT INTO `mistake` VALUES (23, 'JavaScript中用于获取DOM元素的方法是？', 2, '{\"A\": \"getElementById()\", \"B\": \"getElementsByClassName()\", \"C\": \"querySelector()\", \"D\": \"以上都是\"}', 2, 'ABCD', 'D', '2025-08-25 14:39:36', 3, 8);
INSERT INTO `mistake` VALUES (24, 'JavaScript中数组的添加元素方法是？', 2, '{\"A\": \"push()\", \"B\": \"add()\", \"C\": \"append()\", \"D\": \"insert()\"}', 1, 'A', 'C', '2025-08-25 14:39:36', 3, 9);

-- ----------------------------
-- Table structure for question
-- ----------------------------
DROP TABLE IF EXISTS `question`;
CREATE TABLE `question`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '题目ID',
  `exam_id` int(11) NOT NULL COMMENT '所属试卷ID',
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '题目内容',
  `options` json NOT NULL COMMENT '选项内容，以ABCD为选项序号的JSON格式',
  `type` tinyint(1) NOT NULL COMMENT '题目类型：1单选，2多选',
  `analysis` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '题目解析',
  `answer` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '正确答案，存储ABCD格式',
  `score` int(11) NOT NULL DEFAULT 5 COMMENT '分值',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_exam_id`(`exam_id`) USING BTREE,
  INDEX `idx_type`(`type`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE,
  CONSTRAINT `question_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '题目信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of question
-- ----------------------------
INSERT INTO `question` VALUES (1, 1, 'PHP中用于输出字符串的函数是？', '{\"A\": \"echo()\", \"B\": \"print()\", \"C\": \"printf()\", \"D\": \"sprintf()\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (2, 1, 'PHP变量的正确命名方式是？', '{\"A\": \"$变量名\", \"B\": \"@变量名\", \"C\": \"#变量名\", \"D\": \"%变量名\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (3, 1, '以下哪个是PHP的数组函数？', '{\"A\": \"array_push()\", \"B\": \"push_array()\", \"C\": \"array_add()\", \"D\": \"add_array()\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (4, 1, 'PHP中连接数据库的扩展是？', '{\"A\": \"mysqli\", \"B\": \"mysql\", \"C\": \"pdo\", \"D\": \"以上都是\"}', 2, '', 'AD', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (5, 1, 'PHP的全称是什么？', '{\"A\": \"Personal Home Page\", \"B\": \"PHP: Hypertext Preprocessor\", \"C\": \"Private Home Page\", \"D\": \"Professional Home Page\"}', 1, '', 'B', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (6, 2, 'JavaScript中声明变量的关键字是？', '{\"A\": \"var\", \"B\": \"let\", \"C\": \"const\", \"D\": \"以上都是\"}', 2, '', 'ABCD', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (7, 2, '以下哪个不是JavaScript的数据类型？', '{\"A\": \"string\", \"B\": \"number\", \"C\": \"boolean\", \"D\": \"char\"}', 1, '', 'D', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (8, 2, 'JavaScript中用于获取DOM元素的方法是？', '{\"A\": \"getElementById()\", \"B\": \"getElementsByClassName()\", \"C\": \"querySelector()\", \"D\": \"以上都是\"}', 2, '', 'ABCD', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (9, 2, 'JavaScript中数组的添加元素方法是？', '{\"A\": \"push()\", \"B\": \"add()\", \"C\": \"append()\", \"D\": \"insert()\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (10, 3, 'MySQL中用于创建数据库的语句是？', '{\"A\": \"CREATE DATABASE\", \"B\": \"MAKE DATABASE\", \"C\": \"NEW DATABASE\", \"D\": \"BUILD DATABASE\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (11, 3, 'MySQL中主键的关键字是？', '{\"A\": \"PRIMARY KEY\", \"B\": \"MAIN KEY\", \"C\": \"KEY PRIMARY\", \"D\": \"UNIQUE KEY\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (12, 3, '以下哪些是MySQL的数据类型？', '{\"A\": \"INT\", \"B\": \"VARCHAR\", \"C\": \"TEXT\", \"D\": \"STRING\"}', 2, '', 'ABC', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (13, 3, 'MySQL中用于排序的关键字是？', '{\"A\": \"ORDER BY\", \"B\": \"SORT BY\", \"C\": \"GROUP BY\", \"D\": \"ARRANGE BY\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (14, 3, 'MySQL中用于连接查询的关键字是？', '{\"A\": \"JOIN\", \"B\": \"CONNECT\", \"C\": \"LINK\", \"D\": \"COMBINE\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (15, 3, 'MySQL中用于删除表的语句是？', '{\"A\": \"DROP TABLE\", \"B\": \"DELETE TABLE\", \"C\": \"REMOVE TABLE\", \"D\": \"CLEAR TABLE\"}', 1, '', 'A', 5, '2025-08-21 10:10:54');
INSERT INTO `question` VALUES (16, 1, '1. PHP是什么类型的语言？', '{\"A\": \"客户端脚本语言\", \"B\": \"服务器端脚本语言\", \"C\": \"标记语言\", \"D\": \"编译型语言\"}', 1, 'PHP是一种服务器端脚本语言，用于动态网页开发。', 'B', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (17, 1, '2. 以下哪个符号用于PHP变量声明？', '{\"A\": \"!\", \"B\": \"@\", \"C\": \"$\", \"D\": \"#\"}', 1, 'PHP变量以$符号开头，如$var。', 'C', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (18, 1, '3. PHP中输出内容的正确方式是？（多选）', '{\"A\": \"echo\", \"B\": \"print\", \"C\": \"printf\", \"D\": \"console.log\"}', 2, 'PHP中echo、print和printf都可输出内容，console.log是JavaScript的方法。', 'ABC', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (19, 1, '4. PHP文件的默认扩展名是？', '{\"A\": \".ph\", \"B\": \".php\", \"C\": \".phtml\", \"D\": \".html\"}', 1, 'PHP文件的标准扩展名是.php。', 'B', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (20, 1, '5. 以下哪个不是PHP的超全局变量？', '{\"A\": \"$_GET\", \"B\": \"$_POST\", \"C\": \"$_GLOBAL\", \"D\": \"$_SESSION\"}', 1, '正确的超全局变量是$_GLOBALS（带S），不是$_GLOBAL。', 'C', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (21, 1, '6. PHP中连接字符串的运算符是？', '{\"A\": \"+\", \"B\": \".\", \"C\": \"&\", \"D\": \"||\"}', 1, 'PHP使用点号(.)连接字符串，如$str1 . $str2。', 'B', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (22, 1, '7. 以下哪个函数用于包含外部PHP文件？（多选）', '{\"A\": \"include()\", \"B\": \"require()\", \"C\": \"import()\", \"D\": \"load()\"}', 2, 'PHP使用include()和require()包含文件，import()是其他语言的用法。', 'AB', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (23, 1, '8. PHP中如何定义常量？', '{\"A\": \"const MY_CONST\", \"B\": \"define(\'MY_CONST\', value)\", \"C\": \"constant MY_CONST\", \"D\": \"#define MY_CONST\"}', 1, 'PHP使用define()函数定义常量，如define(\'PI\', 3.14)。', 'B', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (24, 1, '9. PHP数组分为哪两种类型？（多选）', '{\"A\": \"索引数组\", \"B\": \"关联数组\", \"C\": \"数字数组\", \"D\": \"对象数组\"}', 2, 'PHP数组分为索引数组（数字键）和关联数组（字符串键）。', 'AB', 5, '2025-08-25 13:18:26');
INSERT INTO `question` VALUES (25, 1, '10. PHP中结束语句的分号是？', '{\"A\": \"必须省略\", \"B\": \"可选\", \"C\": \"必须使用\", \"D\": \"只有特定语句需要\"}', 1, 'PHP中大多数语句必须以分号(;)结尾。', 'C', 5, '2025-08-25 13:18:26');

SET FOREIGN_KEY_CHECKS = 1;
