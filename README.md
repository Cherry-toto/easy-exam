## 项目介绍
在线自学考试系统，包括用户登录注册、试卷列表、在线考试、成绩查询、错题本等功能。

## 技术栈
- 前端：html5+css3+js
- 后端：php
- 数据库：mysql
## 项目结构
- 前端：html5+css3+js
- 后端：php
- 数据库：mysql
## 运行项目
- 前端：直接打开index.html即可
- 后端：php -S localhost:8000
- 数据库：phpmyadmin
- 数据库导入sql文件：exame.sql
## 前端页面结构说明
- login.html：登录（邮箱，邮箱验证码，登录按钮，无需注册）
- index.html：首页（试卷列表，在线考试，成绩查询，错题本）
- exam-list.html：试卷列表(试卷标题，题目数，考试时间，操作按钮)
- exam.html：在线考试页面（从试卷列表进入在线考试，包含题目，选项，提交按钮，题号导航）
- error-list.html：错题本页面(错题标题，题目，选项，用户选择，正确答案)
## 后端接口说明
- 首页页面链接：/index.php
- 登录页面链接：/login.php
- 试卷列表页面链接：/exam-list.php
- 进入考试链接：/exam.php
- 错题本页面链接：/register.php



