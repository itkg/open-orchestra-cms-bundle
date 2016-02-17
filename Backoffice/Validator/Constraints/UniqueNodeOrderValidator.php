<?php

namespace OpenOrchestra\Backoffice\Validator\Constraints;

use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueNodeOrderValidator
 */
class UniqueNodeOrderValidator extends ConstraintValidator
{
    protected $repository;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param NodeInterface $value      The value that should be validated
     * @param Constraint    $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        $result = $this->repository->hasOtherNodeWithSameParentAndOrder(
            $value->getParentId(),
            $value->getOrder(),
            $value->getNodeId(),
            $value->getSiteId()
        );

        if (true === $result) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
