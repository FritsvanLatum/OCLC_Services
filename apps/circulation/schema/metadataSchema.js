
schemaObj =
{
  title: "Collection management information API",
  type: "object",
  properties: {
    ocn: {
      type: "string",
      title: "OCLC Number"
    },
    acc: {
      type: "string",
      format: "radio",
      title: "Format",
      enum: ["xml", "json"]
    }
  }
}
