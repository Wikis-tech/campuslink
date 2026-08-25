export default function VendorLoading() {
  return (
    <main className="portal-shell">
      <div className="cl-skeleton" style={{height:72,borderRadius:0}}/>
      <section style={{width:'min(1180px,calc(100% - 32px))',margin:'30px auto 70px'}}>
        <div className="cl-skeleton" style={{height:270,borderRadius:'6px 28px 6px 28px'}}/>
        <div className="cl-data-rail vendor-data-rail" style={{marginTop:18}}>
          {[0,1,2,3].map((item) => <div key={item}><div className="cl-skeleton" style={{height:12,width:'60%'}}/><div className="cl-skeleton" style={{height:32,width:'34%',marginTop:10}}/></div>)}
        </div>
        <div style={{display:'grid',gridTemplateColumns:'repeat(3,minmax(0,1fr))',gap:16,marginTop:24}}>
          {[0,1,2].map((item) => <div className="cl-skeleton" style={{height:180}} key={item}/>) }
        </div>
      </section>
    </main>
  )
}
