export default function Button({ children, variant = "primary", className = "", ...props }) {
  const baseStyle = "px-[30px] py-[12px] font-sarabun text-[16px] font-medium border-none rounded-md cursor-pointer transition-all flex items-center gap-2";
  
  const variants = {
    primary: "bg-primary text-white hover:bg-primary-light hover:shadow-[0_4px_10px_rgba(26,115,232,0.3)] ml-auto",
    secondary: "bg-[#E9ECEF] text-textMain hover:bg-[#DDE2E5]",
  };

  return (
    <button className={`${baseStyle} ${variants[variant]} ${className}`} {...props}>
      {children}
    </button>
  );
}
