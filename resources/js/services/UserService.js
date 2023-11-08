export const getInitials = (name) => {
    if (!name.includes(" ")) {
        return name.slice(0, 2).toUpperCase();
    }
    const [firstname, lastname] = name.split(" ");

    if (!lastname) {
        return firstname.slice(0, 2).toUpperCase();
    }

    return (firstname[0] + lastname[0]).toUpperCase();
};

export const shortName = (name) => {
  if (!name.includes(" ")) {
    return name;
  }
  const [firstname, lastname] = name.split(" ");

  return firstname + ' ' + lastname[0] + '.';
};

export const hasRoles = (user, roles) => {
  return user.roles.find((i) => roles.includes(i.name)) !== undefined;
};
