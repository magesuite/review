<?php

namespace MageSuite\Review\Console\Command;

class AssignReviewToMultipleStores extends \Symfony\Component\Console\Command\Command
{
    protected \Magento\Framework\App\State $state;

    protected \MageSuite\Review\Service\ReviewMultipleStoreAssignerFactory $multipleStoreAssignerFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Review\Service\ReviewMultipleStoreAssignerFactory $multipleStoreAssignerFactory
    ) {
        parent::__construct();
        $this->state = $state;
        $this->multipleStoreAssignerFactory = $multipleStoreAssignerFactory;
    }

    protected function configure()
    {
        $this->setName('magesuite:review:assign')
            ->setDescription('Assign reviews to multiple stores');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) { //phpcs:ignore
        }

        $this->multipleStoreAssignerFactory->create()->execute();
    }
}
