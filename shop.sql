DROP TABLE IF EXISTS `shop_admin`;
CREATE TABLE IF NOT EXISTS `shop_admin`(
  `adminid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `adminuser` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '管理员账号',
  `adminpass` CHAR(32) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `adminemail` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '管理员电子邮箱',
  `logintime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` BIGINT NOT NULL DEFAULT '0' COMMENT '登录IP',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY(`adminid`),
  UNIQUE shop_admin_adminuser_adminpass(`adminuser`, `adminpass`),
  UNIQUE shop_admin_adminuser_adminemail(`adminuser`, `adminemail`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `shop_admin`(adminuser,adminpass,adminemail,createtime) VALUES('admin', md5('123'), 'shop@imooc.com', UNIX_TIMESTAMP());

DROP TABLE IF EXISTS `shop_user`;
CREATE TABLE IF NOT EXISTS `shop_user`(
  `userid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` VARCHAR(32) NOT NULL DEFAULT '',
  `userpass` CHAR(32) NOT NULL DEFAULT '',
  `useremail` VARCHAR(100) NOT NULL DEFAULT '',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0',
  UNIQUE shop_user_username_userpass(`username`,`userpass`),
  UNIQUE shop_user_useremail_userpass(`useremail`,`userpass`),
  PRIMARY KEY(`userid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `shop_profile`;
CREATE TABLE IF NOT EXISTS `shop_profile`(
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `truename` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `age` TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` ENUM('0','1','2') NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date NOT NULL DEFAULT '2016-01-01' COMMENT '生日',
  `nickname` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `company` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '公司',
  `userid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户的ID',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY(`id`),
  UNIQUE shop_profile_userid(`userid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

# 分类
DROP TABLE IF EXISTS `shop_category`;
CREATE TABLE IF NOT EXISTS `shop_category`(
  `cateid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(32) NOT NULL DEFAULT '',
  `parentid` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`cateid`),
  KEY shop_category_parentid(`parentid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


#产品表
DROP TABLE IF EXISTS `shop_product`;
CREATE TABLE IF NOT EXISTS `shop_product`(
  `productid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '产品ID',
  `cateid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `title` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '标题',
  `descr` TEXT COMMENT '描述',
  `num` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '库存',
  `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `cover` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '封面图片',
  `pics` TEXT COMMENT '所有图片',
  `issale` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '是否促销',
  `ishot` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '是否热卖',
  `istui` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `saleprice` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '',
  `ison` ENUM('0','1') NOT NULL DEFAULT '1' COMMENT '',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  KEY shop_product_cateid(`cateid`),
  KEY shop_product_ison(`ison`)
)ENGINE=InnoDB DEFAULT CHARSET='utf8';

#购物车表
DROP TABLE IF EXISTS `shop_cart`;
CREATE TABLE IF NOT EXISTS `shop_cart`(
  `cartid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `productid` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `productnum` INT UNSIGNED NOT NULL DEFAULT '0',
  `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `userid` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0',
  KEY shop_cart_productid(`productid`),
  KEY shop_cart_userid(`userid`)
)ENGINE=InnoDB DEFAULT CHARSET='utf8';


# 订单表
DROP TABLE IF EXISTS `shop_order`;
CREATE TABLE IF NOT EXISTS `shop_order`(
  `orderid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '订单ID',
  `userid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `addressid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '收货地址，关联收货地址表',
  `amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总价',
  `status` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单状态',
  `expressid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '快递方式',
  `expressno` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '快递单号，可以用来订单跟踪',
  `tradeno` VARCHAR(100) NOT NULL DEFAULT '',
  `tradeext` TEXT,
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  KEY shop_order_userid(`userid`),
  KEY shop_order_addressid(`addressid`),
  KEY shop_order_expressid(`expressid`)
)ENGINE=InnoDB DEFAULT CHARSET='utf8';

# 订单详情表
DROP TABLE IF EXISTS `shop_order_detail`;
CREATE TABLE IF NOT EXISTS `shop_order_detail`(
  `detailid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `productid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品ID',
  `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `productnum` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品数量',
  `orderid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID，关联订单表',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  KEY shop_order_detail_productid(`productid`),
  KEY shop_order_detail_orderid(`orderid`)
)ENGINE=InnoDB DEFAULT CHARSET='utf8';

# 收货地址表
DROP TABLE IF EXISTS `shop_address`;
CREATE TABLE IF NOT EXISTS `shop_address`(
  `addressid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `firstname` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '名',
  `lastname` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '姓',
  `company` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '公司',
  `address` TEXT COMMENT '地址',
  `postcode` CHAR(6) NOT NULL DEFAULT '' COMMENT '邮编',
  `email` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `telephone` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '电话号码',
  `userid` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID，关联用户表',
  `createtime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  KEY shop_address_userid(`userid`)
)ENGINE=InnoDB DEFAULT CHARSET='utf8';




