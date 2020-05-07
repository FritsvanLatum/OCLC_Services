/* 
* Schema for sign-in from
*/

siSchemaObj =
{
  title: " ",
  type: "object",
  properties: {
    email: {
      type: "string",
      format:"email",
      title: "Email address",
      minLength: 1
    },
    password: {
      type: "string",
      format: "password",
      title: "Password",
      minLength: 1
    }
  }
};
