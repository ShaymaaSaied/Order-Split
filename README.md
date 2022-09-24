# Magento 2 Split Order
## How does it work?
This module split customer/guest orders based on preselected product attribute that you can select from the module setting. Split orders save as normal orders but with parent order attribute. Also, it kept the original order.
### This Module allows below features
- Enable/Disable module from configuration.
- Orders splitting based on a selected attribute from the configuration.
- Original order kept. 
- New orders created after original order created automatically
- Adding a new order attribute (Parent Order) to new orders
- Parent order attribute appear on sales orders grid with a simple filter.

## Installation

### Install via composer (recommended)

Run the following command in Magento 2 root folder:
```sh
composer require magearab/ordersplit
```

### Download directly
- Download it
- Upload it using ftp account to app/code/MageArab/OrderSplit/[module files]



## 2. Activation

Run the following command in Magento 2 root folder:
```sh
php bin/magento module:enable MageArab_OrderSplit
```
```sh
php bin/magento setup:upgrade
```

```sh
php bin/magento setup:di:compile
```

```sh
php bin/magento c:f
```

## 3. Configuration

1. Go to **STORES** > **Configuration** > **MageArab** > **Order Split**.
2. Select **Enabled** option to enable the module.
3. Select the attribute that module will split the orders based on it
![Alt text](/images/configuration?raw=true "Split Order Configuration Setting")
