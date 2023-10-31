const useUser = (user) => {
  const getInitials = () => {
    if (!user.name.includes(" ")) {
        return user.name.slice(0, 2).toUpperCase();
    }
    const [firstname, lastname] = user.name.split(" ");

    if (!lastname) {
        return firstname.slice(0, 2).toUpperCase();
    }

    return (firstname[0] + lastname[0]).toUpperCase();
  }

  return {getInitials};
};

export default useUser;
