
# CodeMakerBundle  
  
While developing management applications, we often face a lot of difficulties when implementing a dynamic coding system for our entities such as (customers, suppliers, invoices, products, ...). That's where CodeMaker will make your life mutch easier by offering you a very simple yet powerful coding system.  
  
# Installation  
  
1. `composer require isom/code-maker`  
2. Enable the bundle in AppKernel.php `new SBC\CodeMakerBundle\CodeMakerBundle()`
3. Add this in `config/routing.yml`
	```yml
	code_maker:
	    resource: "@CodeMakerBundle/Controller/"
	    type: annotation
	    prefix: /code-maker
	```
4. Add this in `config/config.yml`
	```yml
	# Code maker Configuration
	code_maker:
	    auto_update_id: true
	    respect_pattern: true
	    cm_form_template: 'native' #by default native|material|altair
	    cm_base_layout: '@your-base-layout.html.twig'
	```
5. Run `php bin/console doctrine:schema:update --force`
	
# Usage
Now let's say that you have an entity called `Product` and you want to generate a new custom string id every time when a new product is created, then you need to do the following things:
## 1. Step 1
Create your `Product` entity and implement the `CodeMaker` annotation
```php
<?php
namespace YourBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use SBC\CodeMakerBundle\Annotation\CodeMaker;
/**
 * Product
 *
 * @ORM\Table(name="Product")
 * @ORM\Entity(repositoryClass="YourBundle\Repository\ProductRepository")
 * 
 * @CodeMaker(  
 *     displayName="Products",  
 *     codeColumn="id"  
 * )
 */
class Product
{
	/**  
	 * @var string 
	 * @ORM\Id 
	 * @ORM\Column(name="id", type="string", length=255) 
	 */
	private $id;
	
	/**  
	 * @var string 
	 * @ORM\Column(name="field;", type="string", length=255) 
	 */
	private $field;

	/**
	 *
	 *
	 * Getters and setters
	 */
}
```
- `displayName` : a simple name that will be used by CodeMakerBundle's coding system
- `codeColumn` : the field considered as unique identifier (it can be the identifier of the entity or another field)
- `discriminatorColumn` : we will see them in a specific part
- `discriminations` : we will see them in a specific part


## 2. Step 2
Now after the implementation of the `CodeMaker` annotation the entity will be known by the coding system and all that remains is to create our famous `Generator`

#### 2.1. Create Generator with console mode
#### 2.2. Create Generator with graphic interface mode