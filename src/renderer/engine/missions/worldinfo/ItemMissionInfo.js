// ItemMissionInfo (H class in MM bundle, offset 1342787).
export class ItemMissionInfo {
  constructor(itemId = '', modelId = '', modelName = '', modelDescription = '', modelTags = '') {
    this.itemId           = itemId
    this.modelId          = modelId
    this.modelName        = modelName
    this.modelDescription = modelDescription
    this.modelTags        = modelTags
  }

  // Construct from a model object (no instance uid)
  static fromModel(model) {
    return new ItemMissionInfo(
      '', String(model?.id ?? ''), String(model?.name ?? ''),
      String(model?.description ?? ''), String(model?.tags ?? ''),
    )
  }

  // Construct from a placed item (has instance uid)
  static fromItem(item) {
    return new ItemMissionInfo(
      String(item?.uid ?? ''),
      String(item?.model?.id          ?? ''),
      String(item?.model?.name        ?? ''),
      String(item?.model?.description ?? ''),
      String(item?.model?.tags        ?? ''),
    )
  }

  clone() { return Object.assign(Object.create(ItemMissionInfo.prototype), this) }
}
