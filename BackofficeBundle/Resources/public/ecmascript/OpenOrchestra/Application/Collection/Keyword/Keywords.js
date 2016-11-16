import DataTableCollection from '../../../Service/DataTable/Collection/DataTableCollection'
import Keyword             from '../../Model/Keyword/Keyword'

/**
 * @class Keywords
 */
class Keywords extends DataTableCollection
{
    /**
     * Pre initialize
     */
    preinitialize() {
        this.model = Keyword;
    }

    /**
     * @inheritdoc
     */
    _getSyncUrl(method) {
        switch (method) {
            case "read":
                return Routing.generate('open_orchestra_api_keyword_list');
        }
    }
}

export default Keywords
