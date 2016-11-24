import OrchestraCollection from '../OrchestraCollection'
import NodeTree            from '../../Model/Node/NodeTree'

/**
 * @class NodesTree
 */
class NodesTree extends OrchestraCollection
{
    /**
     * Pre initialize
     */
    preinitialize() {
        this.model = NodeTree;
    }

    /**
     * @inheritdoc
     */
    _getSyncUrl(method, options) {
        let urlParameter = options.urlParameter || {};
        switch (method) {
            case "read":
                return Routing.generate('open_orchestra_api_node_list_tree', urlParameter);
        }
    }
}

export default NodesTree
