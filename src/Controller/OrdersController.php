<?php

namespace App\Controller;

use App\Entity\ShopOrders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use DirectoryIterator;
use App\Helper\UtilityHelper;

class OrdersController extends AbstractController
{
    private $entityManager;
    private $utilityHelper;
    private $uploadUrl;
    private $uploadDir;
    private $slugger;
    
	public function __construct(
        EntityManagerInterface $entityManager, 
        UtilityHelper $utilityHelper,
        SluggerInterface $slugger
    )
    {
        $this->slugger = $slugger;
        $this->entityManager = $entityManager;	
        $this->utilityHelper = $utilityHelper;	
        $this->uploadUrl = $this->utilityHelper->getRootUrl() . '/uploads/files';
        $this->uploadDir = $this->utilityHelper->getPublicDir() . '/uploads/files';
	}
    


    public function index(Request $request): Response
    {      
        return $this->json(['ok' => 234234]);
    }


	public function getAll(Request $request): Response
	{
        
        
		try {
			$perPage = $request->query->getInt('per-page', 10);
			$page = $request->query->getInt('page', 1);
			$sort = $request->query->get('sort', 'desc'); // Default sort order is descending
			$status = $request->query->get('status'); // Get the status parameter
			$search = $request->query->get('search'); // Get the search parameter
			$country = $request->query->get('country'); // Get the country parameter
			$state = $request->query->get('state'); // Get the state parameter
			$offset = ($page - 1) * $perPage;

			// Determine the sorting order based on the "sort" parameter
			if ($sort === 'id-asc') {
				$order = ['id' => 'ASC'];
			} elseif ($sort === 'id-desc') {
				$order = ['id' => 'DESC'];
			} else {
				$order = ($sort === 'asc') ? ['createdAt' => 'ASC'] : ['createdAt' => 'DESC'];
			}

			$orderRepository = $this->entityManager->getRepository(ShopOrders::class);

            
            //error_log( print_r( $orderRepository ,true) );
           
           //print_r( $orderRepository);
           
           // error_log("XXXX");
            

			// Create criteria for the query
			$queryBuilder = $orderRepository->createQueryBuilder('o');

			if (!empty($status)) {
				$queryBuilder->andWhere('o.status = :status')->setParameter('status', $status);
			}

			if (!empty($search)) {
				$queryBuilder->andWhere('o.firstName LIKE :search OR o.lastName LIKE :search OR o.email LIKE :search')
							 ->setParameter('search', '%' . $search . '%');
			}

			if (!empty($country)) {
				$queryBuilder->andWhere('o.country = :country')->setParameter('country', $country);
			}

			if (!empty($state)) {
				$queryBuilder->andWhere('o.state = :state')->setParameter('state', $state);
			}

			// First count all results that match the criteria
			$totalQuery = clone $queryBuilder;
			$totalOrders = count($totalQuery->getQuery()->getResult());

			// Apply pagination and sorting
			$queryBuilder->orderBy('o.' . key($order), current($order))
						 ->setFirstResult($offset)
						 ->setMaxResults($perPage);

			$orders = $queryBuilder->getQuery()->getResult();

			$orderData = [];
			foreach ($orders as $order) {
				$orderData[] = [
					'id' => $order->getId(),
					'first_name' => $order->getFirstName(),
					'last_name' => $order->getLastName(),
					'email' => $order->getEmail(),
					'address' => $order->getAddress(),
					'address2' => $order->getAddress2(),
					'phone' => $order->getPhone(),
					'country' => $order->getCountry(),
					'state' => $order->getState(),
					'zip' => $order->getZip(),
					'delivery_note' => $order->getDeliveryNote(),
					'product_list' => $order->getProductList(),
					'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
				];
			}

			$paginateData = $this->utilityHelper->paginate($totalOrders, $perPage, $page, 3); // 3 = visible pages

			return $this->json(['total' => $totalOrders, 'orders' => $orderData, 'paginateData' => $paginateData]);
		} catch (\Exception $e) {
			return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
        
        
	}










};
