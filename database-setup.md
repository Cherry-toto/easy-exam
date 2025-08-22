# 数据库设置说明

## 数据库表结构说明

本项目包含以下四个核心数据表：

### 1. 会员表 (member)
- **用途**: 存储用户注册和登录信息
- **字段**: 
  - `id`: 会员唯一标识
  - `email`: 邮箱地址（唯一）
  - `register_time`: 注册时间
  - `login_time`: 最后登录时间

### 2. 试卷表 (exam)
- **用途**: 存储试卷基本信息
- **字段**:
  - `id`: 试卷唯一标识
  - `title`: 试卷标题
  - `nums`: 题目数量
  - `score`: 试卷总分
  - `create_time`: 创建时间
  - `update_time`: 更新时间

### 3. 题目表 (question)
- **用途**: 存储具体题目信息
- **字段**:
  - `id`: 题目唯一标识
  - `exam_id`: 所属试卷ID（外键）
  - `title`: 题目内容
  - `options`: 选项内容（JSON格式，以ABCD为键）
  - `type`: 题目类型（1单选，2多选）
  - `answer`: 正确答案
  - `create_time`: 创建时间

### 4. 错题本 (mistake)
- **用途**: 存储用户做错的题目
- **字段**:
  - `id`: 错题唯一标识
  - `title`: 题目内容
  - `exam_id`: 所属试卷ID
  - `options`: 选项内容（JSON格式）
  - `type`: 题目类型
  - `answer`: 正确答案
  - `errors`: 用户当时回答的错误答案
  - `create_time`: 创建时间
  - `member_id`: 用户ID（外键）
  - `question_id`: 原题目ID（外键）

## 数据库导入步骤

### 方法1：使用phpMyAdmin
1. 打开phpMyAdmin
2. 创建数据库 `exame_system`
3. 选择数据库后点击"导入"
4. 选择 `exame.sql` 文件
5. 点击"执行"完成导入

### 方法2：使用MySQL命令行
```bash
mysql -u root -p
```

```sql
-- 在MySQL命令行中执行
source d:/dev/exame-test/exame.sql
```

### 方法3：使用PHP脚本自动导入
运行项目时，系统会自动检测并初始化数据库。

## 数据库配置

编辑 `config.php` 文件，根据你的MySQL配置修改以下参数：
- `$host`: 数据库主机地址（通常是localhost）
- `$username`: 数据库用户名
- `$password`: 数据库密码
- `$dbname`: 数据库名称（默认exame_system）

## 测试数据

导入SQL文件后，系统会自动插入以下测试数据：

### 测试用户
- test@example.com
- demo@example.com

### 测试试卷
1. PHP基础知识测试（5题，100分）
2. JavaScript基础测试（4题，80分）
3. MySQL数据库测试（6题，120分）

### 测试错题
- 包含一些预设的错题记录，用于测试错题本功能

## 技术说明

- **字符集**: utf8mb4，支持完整Unicode字符
- **存储引擎**: InnoDB，支持事务和外键
- **时间字段**: 使用MySQL自动时间戳
- **JSON字段**: 用于存储选项数据，便于前端解析