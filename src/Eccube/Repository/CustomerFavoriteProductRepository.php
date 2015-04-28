<?php

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\QueryBuilder;

/**
 * CustomerFavoriteProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerFavoriteProductRepository extends EntityRepository
{
    /**
     * @param \Eccube\Entity\Customer $Customer
     * @param \Eccube\Entity\Product $Product
     */
    public function addFavorite(\Eccube\Entity\Customer $Customer, \Eccube\Entity\Product $Product)
    {
        if ($this->isFavorite($Customer, $Product)) {
            return;
        } else {
            $CustomerFavoriteProduct = new \Eccube\Entity\CustomerFavoriteProduct();
            $CustomerFavoriteProduct->setCustomer($Customer);
            $CustomerFavoriteProduct->setProduct($Product);

            $em = $this->getEntityManager();
            $em->persist($CustomerFavoriteProduct);
            $em->flush();
        }
    }


    /**
     * @param \Eccube\Entity\Customer $Customer
     * @param \Eccube\Entity\Product $Product
     * @return bool
     */
    public function isFavorite(\Eccube\Entity\Customer $Customer, \Eccube\Entity\Product $Product)
    {
        $qb = $this->createQueryBuilder('cf')
            ->select('COUNT(cf.Product)')
            ->andWhere('cf.Customer = :Customer AND cf.Product = :Product')
            ->setParameters(array(
                'Customer' => $Customer,
                'Product' => $Product,
            ));
        $count = $qb
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * @param \Eccube\Entity\Customer $Customer
     * @param \Eccube\Entity\Product $Product
     * @return bool
     */
    public function deleteFavorite(\Eccube\Entity\Customer $Customer, \Eccube\Entity\Product $Product)
    {
        $qb = $this->createQueryBuilder('cf')
            ->andWhere('cf.Customer = :Customer AND cf.Product = :Product')
            ->setParameters(array(
                'Customer' => $Customer,
                'Product' => $Product,
            ));

        try {
            $CustomerFavoriteProduct = $qb
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            return false;
        }

        $em = $this->getEntityManager();
        $em->remove($CustomerFavoriteProduct);
        $em->flush();

        return true;
    }

    /**
     * @param \Eccube\Entity\Customer $Customer
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer(\Eccube\Entity\Customer $Customer)
    {
        $qb = $this->createQueryBuilder('cfp')
            ->select('cfp, p')
            ->innerJoin('cfp.Product', 'p')
            ->where('cfp.Customer = :Customer AND p.status = 1')
            ->setParameter('Customer', $Customer);

        // Order By
        $qb->addOrderBy('cfp.create_date', 'DESC');

        return $qb;
    }
}
